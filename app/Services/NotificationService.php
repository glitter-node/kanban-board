<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\BoardMember;
use App\Models\Card;
use App\Models\CardComment;
use App\Models\Notification as UserNotification;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Notify a newly assigned user about card ownership.
     */
    public function notifyAssignment(Card $card, ?User $recipient, ?User $actor = null): ?UserNotification
    {
        if ($recipient === null) {
            return null;
        }

        if ($actor !== null && $recipient->is($actor)) {
            return null;
        }

        return $this->createNotification(
            recipient: $recipient,
            type: 'card.assignment',
            payload: [
                'card_id' => $card->getKey(),
                'board_id' => $card->board_id,
                'actor_user_id' => $actor?->getKey(),
                'title' => $card->title,
            ],
            boardId: $card->board_id,
            cardId: $card->getKey(),
        );
    }

    /**
     * Notify all mentioned users in a comment.
     */
    public function notifyMention(CardComment $comment, iterable $mentionedUsers, ?User $actor = null): Collection
    {
        $notifications = collect();

        foreach ($mentionedUsers as $user) {
            if (! $user instanceof User) {
                continue;
            }

            if ($actor !== null && $user->is($actor)) {
                continue;
            }

            $notification = $this->createNotification(
                recipient: $user,
                type: 'comment.mention',
                payload: [
                    'comment_id' => $comment->getKey(),
                    'card_id' => $comment->card_id,
                    'actor_user_id' => $actor?->getKey(),
                ],
                boardId: $comment->card?->board_id,
                cardId: $comment->card_id,
            );

            if ($notification !== null) {
                $notifications->push($notification);
            }
        }

        return $notifications;
    }

    /**
     * Notify an assignee that a card due date is approaching.
     */
    public function notifyDueDate(Card $card): ?UserNotification
    {
        if ($card->assignee === null || $card->due_at === null) {
            return null;
        }

        return $this->createNotification(
            recipient: $card->assignee,
            type: 'card.due_soon',
            payload: [
                'card_id' => $card->getKey(),
                'board_id' => $card->board_id,
                'due_at' => $card->due_at?->toIso8601String(),
                'title' => $card->title,
            ],
            boardId: $card->board_id,
            cardId: $card->getKey(),
        );
    }

    /**
     * Notify a user that they were invited to a board.
     */
    public function notifyBoardInvite(BoardMember $membership, ?User $actor = null): ?UserNotification
    {
        $membership->loadMissing(['board', 'user']);

        return $this->createNotification(
            recipient: $membership->user,
            type: 'board.invite',
            payload: [
                'board_id' => $membership->board_id,
                'member_id' => $membership->getKey(),
                'actor_user_id' => $actor?->getKey(),
                'role' => $membership->role,
            ],
            boardId: $membership->board_id,
        );
    }

    /**
     * Read a recipient's notification feed.
     */
    public function listNotifications(User $recipient, int $perPage = 25): CursorPaginator
    {
        return UserNotification::query()
            ->forRecipient($recipient)
            ->with(['board', 'card', 'activity'])
            ->recent()
            ->cursorPaginate($perPage);
    }

    private function createNotification(
        User $recipient,
        string $type,
        array $payload,
        ?int $boardId = null,
        ?int $cardId = null,
        ?int $activityId = null,
    ): ?UserNotification {
        $notification = UserNotification::query()->create([
            'user_id' => $recipient->getKey(),
            'type' => $type,
            'board_id' => $boardId,
            'card_id' => $cardId,
            'activity_id' => $activityId,
            'payload_json' => $payload,
            'read_at' => null,
        ]);

        $this->dispatchAfterCommit(new NotificationCreated(
            userId: $recipient->getKey(),
            notification: [
                'id' => $notification->getKey(),
                'type' => $type,
                'board_id' => $boardId,
                'card_id' => $cardId,
                'read_at' => null,
                'created_at' => $notification->created_at?->toISOString(),
            ],
        ));

        return $notification;
    }

    private function dispatchAfterCommit(object $event): void
    {
        DB::afterCommit(static function () use ($event): void {
            event($event);
        });
    }
}
