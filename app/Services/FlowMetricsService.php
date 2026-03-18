<?php

namespace App\Services;

use App\Models\Board;
use App\Models\Card;
use App\Models\CardColumnHistory;
use App\Models\Column;
use App\Models\User;
use Illuminate\Support\Collection;

class FlowMetricsService
{
    public function recordColumnEntry(Card $card, Column $column, ?User $actor = null): void
    {
        CardColumnHistory::query()->create([
            'board_id' => $card->board_id,
            'card_id' => $card->getKey(),
            'column_id' => $column->getKey(),
            'moved_by_user_id' => $actor?->getKey(),
            'entered_at' => now(),
        ]);
    }

    public function recordColumnExit(Card $card, ?User $actor = null): void
    {
        $history = CardColumnHistory::query()
            ->where('card_id', $card->getKey())
            ->whereNull('left_at')
            ->latest('entered_at')
            ->first();

        if (! $history) {
            return;
        }

        $leftAt = now();

        $history->forceFill([
            'left_at' => $leftAt,
            'duration_seconds' => $history->entered_at?->diffInSeconds($leftAt),
            'moved_by_user_id' => $actor?->getKey() ?? $history->moved_by_user_id,
        ])->save();
    }

    public function boardMetrics(Board $board): array
    {
        $cards = Card::query()
            ->forBoard($board)
            ->active()
            ->get(['id', 'column_id', 'created_at', 'blocked', 'moved_to_done_at']);

        $doneCards = $cards->filter(fn (Card $card) => $card->moved_to_done_at !== null);
        $cycleTimes = $doneCards
            ->map(fn (Card $card) => $card->created_at?->diffInMinutes($card->moved_to_done_at) / 60)
            ->filter();

        $histories = CardColumnHistory::query()
            ->where('board_id', $board->getKey())
            ->get(['column_id', 'entered_at', 'duration_seconds']);

        $averageTimePerColumn = $histories
            ->groupBy('column_id')
            ->map(function (Collection $group, string $columnId): array {
                $durations = $group->map(function (CardColumnHistory $history) {
                    if ($history->duration_seconds !== null) {
                        return $history->duration_seconds / 3600;
                    }

                    return $history->entered_at?->diffInMinutes(now()) / 60;
                })->filter();

                return [
                    'column_id' => (int) $columnId,
                    'average_hours' => $durations->isEmpty() ? 0 : round($durations->avg(), 2),
                ];
            })
            ->values()
            ->keyBy('column_id')
            ->all();

        return [
            'average_cycle_time_hours' => $cycleTimes->isEmpty() ? null : round($cycleTimes->avg(), 2),
            'completed_today' => $doneCards->filter(fn (Card $card) => $card->moved_to_done_at?->isSameDay(now()))->count(),
            'completed_this_week' => $doneCards->filter(fn (Card $card) => $card->moved_to_done_at?->greaterThanOrEqualTo(now()->startOfWeek()))->count(),
            'blocked_cards_count' => $cards->where('blocked', true)->count(),
            'average_time_per_column' => $averageTimePerColumn,
        ];
    }

    public function updateCompletionSignals(Card $card, Column $destinationColumn): array
    {
        $isDoneColumn = in_array(mb_strtolower((string) $destinationColumn->type), ['done', 'completed'], true);

        if ($isDoneColumn) {
            return [
                'status' => 'done',
                'completed_at' => $card->completed_at ?? now(),
                'moved_to_done_at' => $card->moved_to_done_at ?? now(),
            ];
        }

        return [
            'status' => $card->status === 'archived' ? 'archived' : 'open',
        ];
    }
}
