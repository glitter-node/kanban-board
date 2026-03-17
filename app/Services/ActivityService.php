<?php

namespace App\Services;

use App\Events\ActivityCreated;
use App\Models\Activity;
use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

class ActivityService
{
    /**
     * Persist an immutable activity row for the given board-scoped action.
     *
     * This method is intentionally thin so it can safely be called inside
     * higher-level service transactions without owning the transaction itself.
     */
    public function logActivity(
        Board $board,
        ?User $actor,
        string $action,
        string $entityType,
        int $entityId,
        array $metadata = [],
    ): Activity {
        $activity = Activity::query()->create([
            'board_id' => $board->getKey(),
            'actor_user_id' => $actor?->getKey(),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'metadata_json' => $metadata,
            'created_at' => now(),
        ]);

        $this->dispatchAfterCommit(new ActivityCreated(
            boardId: $board->getKey(),
            activity: [
                'id' => $activity->getKey(),
                'board_id' => $board->getKey(),
                'actor_user_id' => $actor?->getKey(),
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'action' => $action,
                'created_at' => $activity->created_at?->toISOString(),
            ],
        ));

        return $activity;
    }

    /**
     * Build a compact payload that can be stored in the activity metadata JSON.
     */
    public function buildActivityPayload(
        ?Model $entity = null,
        array $changes = [],
        array $extra = [],
    ): array {
        return array_filter([
            'entity' => $entity ? [
                'id' => $entity->getKey(),
                'type' => class_basename($entity),
            ] : null,
            'changes' => $changes ?: null,
            'extra' => $extra ?: null,
        ], static fn ($value) => $value !== null);
    }

    /**
     * Read a board-scoped activity feed using cursor pagination.
     */
    public function listBoardActivities(Board $board, array $filters = [], int $perPage = 25): CursorPaginator
    {
        $query = Activity::query()
            ->forBoard($board)
            ->with('actor')
            ->latestFirst();

        if (! empty($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['actor_user_id'])) {
            $query->where('actor_user_id', $filters['actor_user_id']);
        }

        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query->cursorPaginate($perPage);
    }

    private function dispatchAfterCommit(object $event): void
    {
        DB::afterCommit(static function () use ($event): void {
            event($event);
        });
    }
}
