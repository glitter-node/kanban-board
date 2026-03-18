<?php

namespace App\Services;

use App\Models\Board;
use App\Models\BoardMember;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BoardService
{
    public function __construct(
        private readonly ActivityService $activityService,
        private readonly AnalyticsService $analyticsService,
    ) {}

    /**
     * Create a new board and its mandatory owner membership row.
     */
    public function createBoard(User $owner, array $attributes): Board
    {
        return DB::transaction(function () use ($owner, $attributes): Board {
            $board = Board::query()->create([
                'owner_user_id' => $owner->getKey(),
                'type' => Arr::get($attributes, 'type', 'personal'),
                'title' => Arr::get($attributes, 'title'),
                'description' => Arr::get($attributes, 'description'),
                'visibility' => Arr::get($attributes, 'visibility', 'private'),
                'is_archived' => false,
                'settings_json' => Arr::get($attributes, 'settings_json'),
            ]);

            BoardMember::query()->create([
                'board_id' => $board->getKey(),
                'user_id' => $owner->getKey(),
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $this->activityService->logActivity(
                board: $board,
                actor: $owner,
                action: 'board.created',
                entityType: 'board',
                entityId: $board->getKey(),
                metadata: $this->activityService->buildActivityPayload($board),
            );

            $this->dispatchAfterCommit('boards.created', [
                'board_id' => $board->getKey(),
            ]);

            $this->analyticsService->record('board_created', $owner, [
                'board_id' => $board->getKey(),
                'board_type' => $board->type,
                'visibility' => $board->visibility,
            ]);

            return $board->load(['owner', 'memberships']);
        });
    }

    /**
     * Update editable board metadata.
     */
    public function updateBoard(Board $board, array $attributes, ?User $actor = null): Board
    {
        return DB::transaction(function () use ($board, $attributes, $actor): Board {
            $before = Arr::only($board->getAttributes(), [
                'title',
                'description',
                'visibility',
                'settings_json',
                'type',
            ]);

            $board->fill(Arr::only($attributes, [
                'title',
                'description',
                'visibility',
                'settings_json',
                'type',
            ]));
            $board->save();

            $changes = array_intersect_key($board->getChanges(), array_flip([
                'title',
                'description',
                'visibility',
                'settings_json',
                'type',
            ]));

            if ($changes !== []) {
                $this->activityService->logActivity(
                    board: $board,
                    actor: $actor,
                    action: 'board.updated',
                    entityType: 'board',
                    entityId: $board->getKey(),
                    metadata: $this->activityService->buildActivityPayload(
                        entity: $board,
                        changes: [
                            'before' => $before,
                            'after' => Arr::only($board->getAttributes(), array_keys($before)),
                        ],
                    ),
                );

                $this->dispatchAfterCommit('boards.updated', [
                    'board_id' => $board->getKey(),
                ]);
            }

            return $board->refresh();
        });
    }

    /**
     * Archive a board without destroying its history.
     */
    public function archiveBoard(Board $board, ?User $actor = null): Board
    {
        return DB::transaction(function () use ($board, $actor): Board {
            if ($board->is_archived) {
                return $board;
            }

            $board->forceFill(['is_archived' => true])->save();

            $this->activityService->logActivity(
                board: $board,
                actor: $actor,
                action: 'board.archived',
                entityType: 'board',
                entityId: $board->getKey(),
                metadata: $this->activityService->buildActivityPayload($board),
            );

            $this->dispatchAfterCommit('boards.archived', [
                'board_id' => $board->getKey(),
            ]);

            return $board->refresh();
        });
    }

    /**
     * Permanently delete a board and all dependent records.
     *
     * Since the board is the aggregate root, deletes intentionally rely on
     * database cascades rather than piecemeal controller-driven cleanup.
     */
    public function deleteBoard(Board $board, ?User $actor = null): void
    {
        DB::transaction(function () use ($board, $actor): void {
            $boardId = $board->getKey();

            if (! $board->exists) {
                throw new DomainException('The given board does not exist.');
            }

            $board->delete();

            $this->dispatchAfterCommit('boards.deleted', [
                'board_id' => $boardId,
                'actor_user_id' => $actor?->getKey(),
            ]);
        });
    }

    /**
     * List boards visible to the given user.
     */
    public function listBoards(User $user): Collection
    {
        return Board::query()
            ->visibleTo($user)
            ->with(['owner'])
            ->withCount(['columns', 'cards'])
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * Load a board detail projection suitable for board-scoped API responses.
     */
    public function getBoard(Board $board): Board
    {
        return $board->load([
            'owner',
            'memberships.user',
            'columns' => fn ($query) => $query->ordered(),
        ]);
    }

}
