<?php

namespace App\Services;

use App\Events\CardArchived;
use App\Events\CardCreated;
use App\Events\CardUpdated;
use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CardService
{
    public function __construct(
        private readonly ActivityService $activityService,
        private readonly NotificationService $notificationService,
        private readonly CardMoveService $cardMoveService,
        private readonly AnalyticsService $analyticsService,
        private readonly FlowMetricsService $flowMetricsService,
    ) {}

    /**
     * Create a new card at the end of a board column using fractional ordering.
     */
    public function createCard(Board $board, Column $column, User $creator, array $attributes): Card
    {
        $this->assertColumnBelongsToBoard($board, $column);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return DB::transaction(function () use ($board, $column, $creator, $attributes): Card {
                    $this->assertWipLimitNotExceeded($column);

                    $lastOrderKey = Card::query()
                        ->forBoard($board)
                        ->forColumn($column)
                        ->lockForUpdate()
                        ->ordered()
                        ->pluck('order_key')
                        ->last();

                    $card = Card::query()->create([
                        'board_id' => $board->getKey(),
                        'column_id' => $column->getKey(),
                        'creator_user_id' => $creator->getKey(),
                        'assigned_user_id' => Arr::get($attributes, 'assigned_user_id'),
                        'title' => Arr::get($attributes, 'title'),
                        'description' => Arr::get($attributes, 'description'),
                        'priority' => Arr::get($attributes, 'priority', 2),
                        'status' => Arr::get($attributes, 'status', 'open'),
                        'blocked' => (bool) Arr::get($attributes, 'blocked', false),
                        'blocked_reason' => Arr::get($attributes, 'blocked_reason'),
                        'order_key' => $this->cardMoveService->generateOrderKeyAfter($lastOrderKey),
                        'due_at' => Arr::get($attributes, 'due_at'),
                        'started_at' => Arr::get($attributes, 'started_at'),
                        'completed_at' => Arr::get($attributes, 'completed_at'),
                        'moved_to_done_at' => Arr::get($attributes, 'completed_at'),
                        'archived_at' => Arr::get($attributes, 'archived_at'),
                        'version' => 1,
                    ]);

                    $this->flowMetricsService->recordColumnEntry($card, $column, $creator);

                    $this->activityService->logActivity(
                        board: $board,
                        actor: $creator,
                        action: 'card.created',
                        entityType: 'card',
                        entityId: $card->getKey(),
                        metadata: $this->activityService->buildActivityPayload($card),
                    );

                    if ($card->assigned_user_id !== null) {
                        $this->notificationService->notifyAssignment($card, $card->assignee, $creator);
                    }

                    $this->analyticsService->record('card_created', $creator, [
                        'board_id' => $board->getKey(),
                        'card_id' => $card->getKey(),
                        'column_id' => $column->getKey(),
                        'priority' => $card->priority,
                    ]);

                    $this->dispatchAfterCommit(new CardCreated(
                        boardId: $board->getKey(),
                        card: $this->cardPayload($card),
                    ));

                    return $card->load(['creator', 'assignee', 'column']);
                });
            } catch (QueryException $exception) {
                if (! $this->isDuplicateOrderKeyException($exception) || $attempt === 4) {
                    throw $exception;
                }
            }
        }

        throw new DomainException('Unable to create the card due to repeated order key collisions.');
    }

    /**
     * Update editable card fields without changing the card's board or order.
     */
    public function updateCard(Board $board, Card $card, array $attributes, ?User $actor = null): Card
    {
        return DB::transaction(function () use ($board, $card, $attributes, $actor): Card {
            $this->assertCardBelongsToBoard($board, $card);

            $before = Arr::only($card->getAttributes(), [
                'title',
                'description',
                'priority',
                'status',
                'blocked',
                'blocked_reason',
                'due_at',
                'started_at',
                'completed_at',
            ]);

            $card->fill(Arr::only($attributes, [
                'title',
                'description',
                'priority',
                'status',
                'blocked',
                'blocked_reason',
                'due_at',
                'started_at',
                'completed_at',
            ]));

            if ($card->blocked !== true) {
                $card->blocked_reason = null;
            }

            if (in_array($card->status, ['done', 'completed'], true) && $card->completed_at === null) {
                $card->completed_at = now();
            }

            if (in_array($card->status, ['done', 'completed'], true) && $card->moved_to_done_at === null) {
                $card->moved_to_done_at = now();
            }

            $card->version++;
            $card->save();

            $this->activityService->logActivity(
                board: $board,
                actor: $actor,
                action: 'card.updated',
                entityType: 'card',
                entityId: $card->getKey(),
                metadata: $this->activityService->buildActivityPayload(
                    entity: $card,
                    changes: [
                        'before' => $before,
                        'after' => Arr::only($card->getAttributes(), array_keys($before)),
                    ],
                ),
            );

            $this->dispatchAfterCommit(new CardUpdated(
                boardId: $board->getKey(),
                card: $this->cardPayload($card),
            ));

            if (
                in_array($card->status, ['done', 'completed'], true)
                || $card->completed_at !== null
            ) {
                $this->analyticsService->record('card_completed', $actor, [
                    'board_id' => $board->getKey(),
                    'card_id' => $card->getKey(),
                    'column_id' => $card->column_id,
                    'completed_at' => $card->completed_at?->toISOString(),
                ]);
            }

            return $card->refresh();
        });
    }

    /**
     * Archive a card while preserving its historical audit trail.
     */
    public function archiveCard(Board $board, Card $card, ?User $actor = null): Card
    {
        return DB::transaction(function () use ($board, $card, $actor): Card {
            $this->assertCardBelongsToBoard($board, $card);

            if ($card->isArchived()) {
                return $card;
            }

            $card->forceFill([
                'status' => 'archived',
                'archived_at' => now(),
                'version' => $card->version + 1,
            ])->save();

            $this->flowMetricsService->recordColumnExit($card, $actor);

            $this->activityService->logActivity(
                board: $board,
                actor: $actor,
                action: 'card.archived',
                entityType: 'card',
                entityId: $card->getKey(),
                metadata: $this->activityService->buildActivityPayload($card),
            );

            $this->dispatchAfterCommit(new CardArchived(
                boardId: $board->getKey(),
                card: $this->cardPayload($card),
            ));

            $this->analyticsService->record('card_deleted', $actor, [
                'board_id' => $board->getKey(),
                'card_id' => $card->getKey(),
                'column_id' => $card->column_id,
                'status' => $card->status,
            ]);

            $this->analyticsService->record('card_completed', $actor, [
                'board_id' => $board->getKey(),
                'card_id' => $card->getKey(),
                'column_id' => $card->column_id,
                'completed_at' => $card->archived_at?->toISOString(),
            ]);

            return $card->refresh();
        });
    }

    /**
     * Reassign a card and emit notifications only when the assignee changes.
     */
    public function assignCard(Board $board, Card $card, ?User $assignee, ?User $actor = null): Card
    {
        return DB::transaction(function () use ($board, $card, $assignee, $actor): Card {
            $this->assertCardBelongsToBoard($board, $card);

            if ($assignee !== null && ! $assignee->isMemberOf($board)) {
                throw new DomainException('The assignee must be an active board member.');
            }

            $previousAssigneeId = $card->assigned_user_id;

            $card->forceFill([
                'assigned_user_id' => $assignee?->getKey(),
                'version' => $card->version + 1,
            ])->save();

            $this->activityService->logActivity(
                board: $board,
                actor: $actor,
                action: 'card.assigned',
                entityType: 'card',
                entityId: $card->getKey(),
                metadata: $this->activityService->buildActivityPayload(
                    entity: $card,
                    changes: [
                        'before' => ['assigned_user_id' => $previousAssigneeId],
                        'after' => ['assigned_user_id' => $card->assigned_user_id],
                    ],
                ),
            );

            if ($assignee !== null && $assignee->getKey() !== $previousAssigneeId) {
                $this->notificationService->notifyAssignment($card, $assignee, $actor);
            }

            $this->dispatchAfterCommit(new CardUpdated(
                boardId: $board->getKey(),
                card: $this->cardPayload($card),
            ));

            return $card->refresh();
        });
    }

    /**
     * Return cards for a board with optional board-scoped filters.
     */
    public function listCards(Board $board, array $filters = [], int $perPage = 50): CursorPaginator
    {
        $query = Card::query()
            ->forBoard($board)
            ->with(['column', 'creator', 'assignee'])
            ->active()
            ->orderBy('column_id')
            ->ordered();

        if (! empty($filters['column_id'])) {
            $query->where('column_id', $filters['column_id']);
        }

        if (! empty($filters['assigned_user_id'])) {
            $query->assignedTo($filters['assigned_user_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (array_key_exists('due_soon_hours', $filters) && $filters['due_soon_hours'] !== null) {
            $query->dueSoon((int) $filters['due_soon_hours']);
        }

        return $query->cursorPaginate($perPage);
    }

    /**
     * Load a single board-scoped card projection.
     */
    public function getCard(Board $board, Card $card): Card
    {
        $this->assertCardBelongsToBoard($board, $card);

        return $card->load(['board', 'column', 'creator', 'assignee']);
    }

    private function assertColumnBelongsToBoard(Board $board, Column $column): void
    {
        if (! $column->belongsToBoard($board)) {
            throw new DomainException('The column does not belong to the given board.');
        }
    }

    private function assertCardBelongsToBoard(Board $board, Card $card): void
    {
        if (! $card->belongsToBoard($board)) {
            throw new DomainException('The card does not belong to the given board.');
        }
    }

    private function isDuplicateOrderKeyException(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;
        $driverCode = $exception->errorInfo[1] ?? null;

        return $sqlState === '23000' && (int) $driverCode === 1062;
    }

    private function cardPayload(Card $card): array
    {
        return [
            'id' => $card->getKey(),
            'board_id' => $card->board_id,
            'column_id' => $card->column_id,
            'order_key' => $card->order_key,
            'title' => $card->title,
            'description' => $card->description,
            'assigned_user_id' => $card->assigned_user_id,
            'priority' => $card->priority,
            'status' => $card->status,
            'blocked' => $card->blocked,
            'blocked_reason' => $card->blocked_reason,
            'due_at' => $card->due_at?->toISOString(),
            'created_at' => $card->created_at?->toISOString(),
            'completed_at' => $card->completed_at?->toISOString(),
            'moved_to_done_at' => $card->moved_to_done_at?->toISOString(),
            'updated_at' => $card->updated_at?->toISOString(),
        ];
    }

    private function assertWipLimitNotExceeded(Column $column): void
    {
        if ($column->wip_limit === null) {
            return;
        }

        $activeCards = Card::query()
            ->forColumn($column)
            ->where('status', '!=', 'archived')
            ->count();

        if ($activeCards >= $column->wip_limit) {
            throw new DomainException('This column has reached its WIP limit.');
        }
    }

    private function dispatchAfterCommit(object $event): void
    {
        DB::afterCommit(static function () use ($event): void {
            event($event);
        });
    }
}
