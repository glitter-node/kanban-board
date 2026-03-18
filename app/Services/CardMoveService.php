<?php

namespace App\Services;

use App\Events\CardMoved;
use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\User;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CardMoveService
{
    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyz';

    public function __construct(
        private readonly ActivityService $activityService,
        private readonly AnalyticsService $analyticsService,
    ) {}

    /**
     * Move a card within its current column by generating a new order key
     * between the supplied neighbors.
     */
    public function moveCardWithinColumn(
        Board $board,
        Card $card,
        ?Card $previousCard = null,
        ?Card $nextCard = null,
        ?User $actor = null,
    ): Card {
        return $this->moveCard(
            board: $board,
            card: $card,
            destinationColumn: $card->column,
            previousCard: $previousCard,
            nextCard: $nextCard,
            actor: $actor,
            action: 'card.moved_within_column',
        );
    }

    /**
     * Move a card across columns using fractional ordering in the destination.
     */
    public function moveCardAcrossColumns(
        Board $board,
        Card $card,
        Column $destinationColumn,
        ?Card $previousCard = null,
        ?Card $nextCard = null,
        ?User $actor = null,
    ): Card {
        return $this->moveCard(
            board: $board,
            card: $card,
            destinationColumn: $destinationColumn,
            previousCard: $previousCard,
            nextCard: $nextCard,
            actor: $actor,
            action: 'card.moved_across_columns',
        );
    }

    /**
     * Move a card using a precomputed fractional order key supplied by the caller.
     *
     * This is useful when the client already knows the target position token.
     */
    public function moveCardToOrderKey(
        Board $board,
        Card $card,
        Column $destinationColumn,
        string $orderKey,
        ?User $actor = null,
    ): Card {
        $this->assertCardBelongsToBoard($board, $card);
        $this->assertColumnBelongsToBoard($board, $destinationColumn);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return DB::transaction(function () use ($board, $card, $destinationColumn, $orderKey, $actor): Card {
                    /** @var Card $lockedCard */
                    $lockedCard = Card::query()
                        ->whereKey($card->getKey())
                        ->lockForUpdate()
                        ->firstOrFail();

                    $fromColumnId = $lockedCard->column_id;

                    $lockedCard->forceFill([
                        'column_id' => $destinationColumn->getKey(),
                        'order_key' => $orderKey,
                        'version' => $lockedCard->version + 1,
                    ])->save();

                    $this->activityService->logActivity(
                        board: $board,
                        actor: $actor,
                        action: 'card.moved',
                        entityType: 'card',
                        entityId: $lockedCard->getKey(),
                        metadata: $this->activityService->buildActivityPayload(
                            entity: $lockedCard,
                            changes: [
                                'before' => ['column_id' => $fromColumnId],
                                'after' => ['column_id' => $destinationColumn->getKey()],
                            ],
                            extra: [
                                'from_column_id' => $fromColumnId,
                                'to_column_id' => $destinationColumn->getKey(),
                                'order_key' => $orderKey,
                            ],
                        ),
                    );

                    $this->dispatchAfterCommit(new CardMoved(
                        boardId: $board->getKey(),
                        card: [
                            'id' => $lockedCard->getKey(),
                            'column_id' => $destinationColumn->getKey(),
                            'order_key' => $orderKey,
                            'updated_at' => $lockedCard->updated_at?->toISOString(),
                        ],
                    ));

                    $this->analyticsService->record('card_moved', $actor, [
                        'board_id' => $board->getKey(),
                        'card_id' => $lockedCard->getKey(),
                        'from_column_id' => $fromColumnId,
                        'to_column_id' => $destinationColumn->getKey(),
                        'order_key' => $orderKey,
                    ]);

                    return $lockedCard->refresh();
                });
            } catch (QueryException $exception) {
                if (! $this->isDuplicateOrderKeyException($exception) || $attempt === 4) {
                    throw $exception;
                }
            }
        }

        throw new DomainException('Unable to move the card due to repeated order key collisions.');
    }

    /**
     * Generate a lexicographically sortable key strictly between $left and $right.
     *
     * The implementation uses a variable-length base36 alphabet and avoids
     * rewriting neighboring rows, which is the core scalability benefit of
     * fractional ordering over dense integer indexing.
     */
    public function generateOrderKeyBetween(?string $left, ?string $right): string
    {
        if ($left !== null && $right !== null && strcmp($left, $right) >= 0) {
            throw new DomainException('Left order key must be smaller than right order key.');
        }

        $base = strlen(self::ALPHABET);
        $prefix = '';
        $index = 0;

        while (true) {
            $leftDigit = $this->digitAt($left, $index, 0);
            $rightDigit = $this->digitAt($right, $index, $base - 1);

            if ($rightDigit - $leftDigit > 1) {
                $midpoint = intdiv($leftDigit + $rightDigit, 2);

                return $prefix.self::ALPHABET[$midpoint];
            }

            $prefix .= self::ALPHABET[$leftDigit];
            $index++;
        }
    }

    public function generateOrderKeyBefore(?string $right): string
    {
        return $this->generateOrderKeyBetween(null, $right);
    }

    public function generateOrderKeyAfter(?string $left): string
    {
        return $this->generateOrderKeyBetween($left, null);
    }

    private function moveCard(
        Board $board,
        Card $card,
        Column $destinationColumn,
        ?Card $previousCard,
        ?Card $nextCard,
        ?User $actor,
        string $action,
    ): Card {
        $this->assertCardBelongsToBoard($board, $card);
        $this->assertColumnBelongsToBoard($board, $destinationColumn);
        $this->assertNeighborBelongsToDestination($board, $destinationColumn, $previousCard);
        $this->assertNeighborBelongsToDestination($board, $destinationColumn, $nextCard);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return DB::transaction(function () use (
                    $board,
                    $card,
                    $destinationColumn,
                    $previousCard,
                    $nextCard,
                    $actor,
                    $action,
                ): Card {
                    /** @var Card $lockedCard */
                    $lockedCard = Card::query()
                        ->whereKey($card->getKey())
                        ->lockForUpdate()
                        ->firstOrFail();

                    $fromColumnId = $lockedCard->column_id;

                    $freshPrevious = $previousCard
                        ? Card::query()->whereKey($previousCard->getKey())->lockForUpdate()->first()
                        : null;

                    $freshNext = $nextCard
                        ? Card::query()->whereKey($nextCard->getKey())->lockForUpdate()->first()
                        : null;

                    $leftKey = $freshPrevious?->order_key;
                    $rightKey = $freshNext?->order_key;

                    if ($freshPrevious && $freshPrevious->column_id !== $destinationColumn->getKey()) {
                        throw new DomainException('The previous card does not belong to the destination column.');
                    }

                    if ($freshNext && $freshNext->column_id !== $destinationColumn->getKey()) {
                        throw new DomainException('The next card does not belong to the destination column.');
                    }

                    $newOrderKey = match (true) {
                        $leftKey !== null && $rightKey !== null => $this->generateOrderKeyBetween($leftKey, $rightKey),
                        $leftKey !== null => $this->generateOrderKeyAfter($leftKey),
                        $rightKey !== null => $this->generateOrderKeyBefore($rightKey),
                        default => $this->generateOrderKeyBetween(null, null),
                    };

                    $lockedCard->forceFill([
                        'board_id' => $board->getKey(),
                        'column_id' => $destinationColumn->getKey(),
                        'order_key' => $newOrderKey,
                        'version' => $lockedCard->version + 1,
                    ])->save();

                    $this->activityService->logActivity(
                        board: $board,
                        actor: $actor,
                        action: $action,
                        entityType: 'card',
                        entityId: $lockedCard->getKey(),
                        metadata: $this->activityService->buildActivityPayload(
                            entity: $lockedCard,
                            changes: [
                                'before' => ['column_id' => $fromColumnId],
                                'after' => ['column_id' => $destinationColumn->getKey()],
                            ],
                            extra: [
                                'from_column_id' => $fromColumnId,
                                'to_column_id' => $destinationColumn->getKey(),
                                'order_key' => $newOrderKey,
                            ],
                        ),
                    );

                    $this->dispatchAfterCommit(new CardMoved(
                        boardId: $board->getKey(),
                        card: [
                            'id' => $lockedCard->getKey(),
                            'column_id' => $destinationColumn->getKey(),
                            'order_key' => $orderKey,
                            'updated_at' => $lockedCard->updated_at?->toISOString(),
                        ],
                    ));

                    $this->analyticsService->record('card_moved', $actor, [
                        'board_id' => $board->getKey(),
                        'card_id' => $lockedCard->getKey(),
                        'from_column_id' => $fromColumnId,
                        'to_column_id' => $destinationColumn->getKey(),
                        'order_key' => $newOrderKey,
                    ]);

                    return $lockedCard->refresh();
                });
            } catch (QueryException $exception) {
                if (! $this->isDuplicateOrderKeyException($exception) || $attempt === 4) {
                    throw $exception;
                }
            }
        }

        throw new DomainException('Unable to move the card due to repeated order key collisions.');
    }

    private function digitAt(?string $value, int $index, int $default): int
    {
        if ($value === null || $index >= strlen($value)) {
            return $default;
        }

        $digit = strpos(self::ALPHABET, $value[$index]);

        if ($digit === false) {
            throw new DomainException('Order keys may only contain lowercase base36 characters.');
        }

        return $digit;
    }

    private function assertCardBelongsToBoard(Board $board, Card $card): void
    {
        if (! $card->belongsToBoard($board)) {
            throw new DomainException('The card does not belong to the given board.');
        }
    }

    private function assertColumnBelongsToBoard(Board $board, Column $column): void
    {
        if (! $column->belongsToBoard($board)) {
            throw new DomainException('The column does not belong to the given board.');
        }
    }

    private function assertNeighborBelongsToDestination(Board $board, Column $destinationColumn, ?Card $neighbor): void
    {
        if ($neighbor === null) {
            return;
        }

        if (! $neighbor->belongsToBoard($board)) {
            throw new DomainException('A neighbor card does not belong to the given board.');
        }

        if ((int) $neighbor->column_id !== (int) $destinationColumn->getKey()) {
            throw new DomainException('A neighbor card does not belong to the destination column.');
        }
    }

    private function isDuplicateOrderKeyException(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;
        $driverCode = $exception->errorInfo[1] ?? null;

        return $sqlState === '23000' && (int) $driverCode === 1062;
    }

    private function dispatchAfterCommit(object $event): void
    {
        DB::afterCommit(static function () use ($event): void {
            event($event);
        });
    }
}
