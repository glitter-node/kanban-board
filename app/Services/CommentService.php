<?php

namespace App\Services;

use App\Events\CommentCreated;
use App\Models\Board;
use App\Models\Card;
use App\Models\CardComment;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CommentService
{
    public function __construct(
        private readonly ActivityService $activityService,
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Create a comment on a board-scoped card and notify mentioned members.
     */
    public function createComment(Board $board, Card $card, User $author, array $attributes): CardComment
    {
        return DB::transaction(function () use ($board, $card, $author, $attributes): CardComment {
            $this->assertCardBelongsToBoard($board, $card);

            $body = (string) ($attributes['body'] ?? '');
            $mentionedUsers = $this->extractMentions($board, $body)
                ->reject(fn (User $user) => $user->is($author))
                ->values();

            $comment = CardComment::query()->create([
                'card_id' => $card->getKey(),
                'user_id' => $author->getKey(),
                'body' => $body,
                'mentions_json' => $mentionedUsers->map(fn (User $user) => [
                    'id' => $user->getKey(),
                    'name' => $user->name,
                ])->all(),
            ]);

            $this->activityService->logActivity(
                board: $board,
                actor: $author,
                action: 'comment.created',
                entityType: 'card_comment',
                entityId: $comment->getKey(),
                metadata: $this->activityService->buildActivityPayload(
                    entity: $comment,
                    extra: ['card_id' => $card->getKey()],
                ),
            );

            if ($mentionedUsers->isNotEmpty()) {
                $this->notificationService->notifyMention($comment, $mentionedUsers, $author);
            }

            $this->dispatchAfterCommit(new CommentCreated(
                boardId: $board->getKey(),
                comment: [
                    'id' => $comment->getKey(),
                    'card_id' => $comment->card_id,
                    'user_id' => $comment->user_id,
                    'body' => $comment->body,
                    'created_at' => $comment->created_at?->toISOString(),
                ],
            ));

            return $comment->load('author');
        });
    }

    /**
     * Update a comment body and refresh mention notifications for the new set.
     */
    public function updateComment(Board $board, Card $card, CardComment $comment, array $attributes, ?User $actor = null): CardComment
    {
        return DB::transaction(function () use ($board, $card, $comment, $attributes, $actor): CardComment {
            $this->assertCommentBelongsToCard($board, $card, $comment);

            $previousBody = $comment->body;
            $body = (string) ($attributes['body'] ?? $comment->body);

            $mentionedUsers = $this->extractMentions($board, $body)
                ->reject(fn (User $user) => $actor !== null && $user->is($actor))
                ->values();

            $comment->forceFill([
                'body' => $body,
                'mentions_json' => $mentionedUsers->map(fn (User $user) => [
                    'id' => $user->getKey(),
                    'name' => $user->name,
                ])->all(),
            ])->save();

            $this->activityService->logActivity(
                board: $board,
                actor: $actor,
                action: 'comment.updated',
                entityType: 'card_comment',
                entityId: $comment->getKey(),
                metadata: $this->activityService->buildActivityPayload(
                    entity: $comment,
                    changes: [
                        'before' => ['body' => $previousBody],
                        'after' => ['body' => $body],
                    ],
                ),
            );

            if ($mentionedUsers->isNotEmpty()) {
                $this->notificationService->notifyMention($comment, $mentionedUsers, $actor);
            }

            return $comment->refresh();
        });
    }

    /**
     * Delete a card comment.
     */
    public function deleteComment(Board $board, Card $card, CardComment $comment, ?User $actor = null): void
    {
        DB::transaction(function () use ($board, $card, $comment, $actor): void {
            $this->assertCommentBelongsToCard($board, $card, $comment);

            $commentId = $comment->getKey();
            $comment->delete();

            $this->activityService->logActivity(
                board: $board,
                actor: $actor,
                action: 'comment.deleted',
                entityType: 'card_comment',
                entityId: $commentId,
                metadata: $this->activityService->buildActivityPayload(
                    extra: ['card_id' => $card->getKey()],
                ),
            );

        });
    }

    /**
     * Extract @mentions from comment text.
     *
     * The current implementation resolves exact matches against a board
     * member's lowercase name or full lowercase email address.
     */
    public function extractMentions(Board $board, string $body): Collection
    {
        preg_match_all('/@([\p{L}\p{N}\.\-_@]+)/u', $body, $matches);

        $tokens = collect($matches[1] ?? [])
            ->map(static fn (string $token) => mb_strtolower(trim($token)))
            ->filter()
            ->unique()
            ->values();

        if ($tokens->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereHas('boardMemberships', function ($query) use ($board) {
                $query->where('board_id', $board->getKey())
                    ->where('status', 'active');
            })
            ->where(function ($query) use ($tokens) {
                foreach ($tokens as $token) {
                    $query->orWhereRaw('LOWER(name) = ?', [$token])
                        ->orWhereRaw('LOWER(email) = ?', [$token]);
                }
            })
            ->get();
    }

    /**
     * Return a cursor-paginated comment feed for a board-scoped card.
     */
    public function listComments(Board $board, Card $card, int $perPage = 50): CursorPaginator
    {
        $this->assertCardBelongsToBoard($board, $card);

        return CardComment::query()
            ->forCard($card)
            ->with('author')
            ->latestFirst()
            ->cursorPaginate($perPage);
    }

    private function assertCardBelongsToBoard(Board $board, Card $card): void
    {
        if (! $card->belongsToBoard($board)) {
            throw new DomainException('The card does not belong to the given board.');
        }
    }

    private function assertCommentBelongsToCard(Board $board, Card $card, CardComment $comment): void
    {
        $this->assertCardBelongsToBoard($board, $card);

        if ((int) $comment->card_id !== (int) $card->getKey()) {
            throw new DomainException('The comment does not belong to the given card.');
        }
    }

    private function dispatchAfterCommit(object $event): void
    {
        DB::afterCommit(static function () use ($event): void {
            event($event);
        });
    }
}
