<?php

namespace App\Services;

use App\Events\ColumnArchived;
use App\Events\ColumnCreated;
use App\Events\ColumnReordered;
use App\Events\ColumnUpdated;
use App\Models\Board;
use App\Models\Column;
use App\Models\User;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ColumnService
{
    public function __construct(
        private readonly ActivityService $activityService,
        private readonly CardMoveService $cardMoveService,
    ) {}

    /**
     * Create a new column at the end of the board's ordered column list.
     */
    public function createColumn(Board $board, array $attributes, ?User $actor = null): Column
    {
        return DB::transaction(function () use ($board, $attributes, $actor): Column {
            $lastOrderKey = $board->columns()->lockForUpdate()->ordered()->pluck('order_key')->last();

            $column = Column::query()->create([
                'board_id' => $board->getKey(),
                'title' => Arr::get($attributes, 'title'),
                'type' => Arr::get($attributes, 'type'),
                'order_key' => $this->cardMoveService->generateOrderKeyAfter($lastOrderKey),
                'wip_limit' => Arr::get($attributes, 'wip_limit'),
                'is_archived' => false,
            ]);

            $this->activityService->logActivity(
                board: $board,
                actor: $actor,
                action: 'column.created',
                entityType: 'column',
                entityId: $column->getKey(),
                metadata: $this->activityService->buildActivityPayload($column),
            );

            $this->dispatchAfterCommit(new ColumnCreated(
                boardId: $board->getKey(),
                column: $this->columnPayload($column),
            ));

            return $column;
        });
    }

    /**
     * Update mutable column metadata.
     */
    public function updateColumn(Board $board, Column $column, array $attributes, ?User $actor = null): Column
    {
        return DB::transaction(function () use ($board, $column, $attributes, $actor): Column {
            $this->assertColumnBelongsToBoard($board, $column);

            $before = Arr::only($column->getAttributes(), ['title', 'type', 'wip_limit']);

            $column->fill(Arr::only($attributes, ['title', 'type', 'wip_limit']));
            $column->save();

            $this->activityService->logActivity(
                board: $board,
                actor: $actor,
                action: 'column.updated',
                entityType: 'column',
                entityId: $column->getKey(),
                metadata: $this->activityService->buildActivityPayload(
                    entity: $column,
                    changes: [
                        'before' => $before,
                        'after' => Arr::only($column->getAttributes(), array_keys($before)),
                    ],
                ),
            );

            $this->dispatchAfterCommit(new ColumnUpdated(
                boardId: $board->getKey(),
                column: $this->columnPayload($column),
            ));

            return $column->refresh();
        });
    }

    /**
     * Archive a column and archive the cards it contains.
     */
    public function archiveColumn(Board $board, Column $column, ?User $actor = null): Column
    {
        return DB::transaction(function () use ($board, $column, $actor): Column {
            $this->assertColumnBelongsToBoard($board, $column);

            if ($column->is_archived) {
                return $column;
            }

            $column->forceFill(['is_archived' => true])->save();

            $column->cards()
                ->where('status', '!=', 'archived')
                ->update([
                    'status' => 'archived',
                    'archived_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->activityService->logActivity(
                board: $board,
                actor: $actor,
                action: 'column.archived',
                entityType: 'column',
                entityId: $column->getKey(),
                metadata: $this->activityService->buildActivityPayload(
                    entity: $column,
                    extra: ['archived_cards_count' => $column->cards()->count()],
                ),
            );

            $this->dispatchAfterCommit(new ColumnArchived(
                boardId: $board->getKey(),
                column: $this->columnPayload($column),
            ));

            return $column->refresh();
        });
    }

    /**
     * Reorder all active columns by rewriting their fractional order keys in
     * the supplied order. This keeps the UI deterministic after drag/drop.
     */
    public function reorderColumns(Board $board, array $orderedColumnIds, ?User $actor = null): Collection
    {
        return DB::transaction(function () use ($board, $orderedColumnIds, $actor): Collection {
            $columns = $board->columns()
                ->whereIn('id', $orderedColumnIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($columns->count() !== count(array_unique($orderedColumnIds))) {
                throw new DomainException('One or more columns do not belong to the given board.');
            }

            $previousKey = null;

            foreach ($orderedColumnIds as $columnId) {
                /** @var Column $column */
                $column = $columns->get($columnId);
                $nextKey = $this->cardMoveService->generateOrderKeyAfter($previousKey);
                $column->forceFill(['order_key' => $nextKey])->save();
                $previousKey = $nextKey;
            }

            $this->activityService->logActivity(
                board: $board,
                actor: $actor,
                action: 'column.reordered',
                entityType: 'board',
                entityId: $board->getKey(),
                metadata: $this->activityService->buildActivityPayload(
                    extra: ['column_ids' => array_values($orderedColumnIds)],
                ),
            );

            $this->dispatchAfterCommit(new ColumnReordered(
                boardId: $board->getKey(),
                columns: $board->columns()
                    ->ordered()
                    ->get(['id', 'board_id', 'order_key', 'updated_at'])
                    ->map(fn (Column $column) => $this->columnPayload($column, ['id', 'board_id', 'order_key', 'updated_at']))
                    ->values()
                    ->all(),
            ));

            return $board->columns()->ordered()->get();
        });
    }

    /**
     * Return ordered columns for a board.
     */
    public function listColumns(Board $board): Collection
    {
        return $board->columns()
            ->active()
            ->ordered()
            ->get();
    }

    private function assertColumnBelongsToBoard(Board $board, Column $column): void
    {
        if (! $column->belongsToBoard($board)) {
            throw new DomainException('The column does not belong to the given board.');
        }
    }

    private function columnPayload(Column $column, ?array $only = null): array
    {
        $payload = [
            'id' => $column->getKey(),
            'board_id' => $column->board_id,
            'title' => $column->title,
            'type' => $column->type,
            'order_key' => $column->order_key,
            'wip_limit' => $column->wip_limit,
            'is_archived' => $column->is_archived,
            'updated_at' => $column->updated_at?->toISOString(),
        ];

        if ($only === null) {
            return $payload;
        }

        return array_intersect_key($payload, array_flip($only));
    }

    private function dispatchAfterCommit(object $event): void
    {
        DB::afterCommit(static function () use ($event): void {
            event($event);
        });
    }
}
