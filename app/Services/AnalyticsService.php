<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserEvent;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AnalyticsService
{
    public function record(
        string $eventName,
        Authenticatable|User|int|null $user = null,
        array $payload = [],
        CarbonInterface|string|null $createdAt = null,
    ): void {
        $this->recordBatch($user, [[
            'event_name' => $eventName,
            'payload' => $payload,
            'created_at' => $createdAt,
        ]]);
    }

    public function recordBatch(Authenticatable|User|int|null $user, array $events): void
    {
        if ($events === []) {
            return;
        }

        $userId = $user instanceof Authenticatable || $user instanceof User
            ? $user->getAuthIdentifier()
            : $user;

        $rows = collect($events)
            ->filter(fn (array $event) => filled($event['event_name'] ?? null))
            ->map(function (array $event) use ($userId): array {
                return [
                    'user_id' => $userId,
                    'event_name' => (string) $event['event_name'],
                    'payload' => $this->sanitizePayload((array) ($event['payload'] ?? [])),
                    'created_at' => $this->normalizeTimestamp($event['created_at'] ?? null),
                ];
            })
            ->values()
            ->all();

        if ($rows === []) {
            return;
        }

        UserEvent::query()->insert($rows);
    }

    public function dashboardMetrics(): array
    {
        $events = UserEvent::query()
            ->orderBy('created_at')
            ->get();

        $topActions = $events->groupBy('event_name')
            ->map(fn (Collection $group, string $eventName) => [
                'event_name' => $eventName,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->take(10)
            ->values();

        $sessionGroups = $events
            ->filter(fn (UserEvent $event) => filled($event->payload['session_id'] ?? null))
            ->groupBy(fn (UserEvent $event) => $event->payload['session_id']);

        $meaningfulActions = collect([
            'board_created',
            'card_created',
            'card_moved',
            'card_deleted',
            'column_created',
            'comment_created',
            'feedback_submitted',
        ]);

        $timeToFirstAction = $sessionGroups
            ->map(function (Collection $sessionEvents) use ($meaningfulActions) {
                $start = $sessionEvents->firstWhere('event_name', 'session_started');
                $firstAction = $sessionEvents->first(fn (UserEvent $event) => $meaningfulActions->contains($event->event_name));

                if (! $start || ! $firstAction) {
                    return null;
                }

                return $start->created_at->diffInSeconds($firstAction->created_at);
            })
            ->filter();

        $dragCompletedCount = $events->where('event_name', 'drag_completed')->count();
        $buttonMoveCount = $events->where('event_name', 'card_move_button_used')->count();
        $dragUsageBase = $dragCompletedCount + $buttonMoveCount;

        $featureAdoption = [
            'comments' => $events->where('event_name', 'comment_created')->count(),
            'members' => $events->where('event_name', 'member_added')->count(),
            'notifications' => $events->where('event_name', 'notification_opened')->count(),
        ];

        $returningUsers = $events
            ->filter(fn (UserEvent $event) => $event->user_id !== null)
            ->groupBy('user_id')
            ->filter(function (Collection $userEvents) {
                return $userEvents
                    ->map(fn (UserEvent $event) => $event->created_at->toDateString())
                    ->unique()
                    ->count() > 1;
            })
            ->count();

        $activeUsers = $events->pluck('user_id')->filter()->unique()->count();

        $funnel = $this->buildFunnel($sessionGroups);
        $cycleTime = $this->averageCycleTime($events);
        $timePerColumn = $this->averageTimePerColumn($events);
        $blockedTasks = $this->blockedTasks($events);
        $uxIssues = $events->where('event_name', 'ux_issue_detected')
            ->groupBy(fn (UserEvent $event) => $event->payload['issue'] ?? 'unknown')
            ->map(fn (Collection $group, string $issue) => ['issue' => $issue, 'count' => $group->count()])
            ->values();

        return [
            'summary' => [
                'events_total' => $events->count(),
                'sessions_total' => $sessionGroups->count(),
                'task_success_rate' => $funnel['task_success_rate'],
                'time_to_first_action_seconds' => $timeToFirstAction->isEmpty() ? null : round($timeToFirstAction->avg(), 2),
                'drag_usage_rate' => $dragUsageBase > 0 ? round(($dragCompletedCount / $dragUsageBase) * 100, 2) : null,
                'returning_users' => $returningUsers,
                'active_users' => $activeUsers,
                'average_cycle_time_hours' => $cycleTime,
            ],
            'top_actions' => $topActions,
            'feature_adoption' => $featureAdoption,
            'funnel' => $funnel['steps'],
            'time_per_column' => $timePerColumn,
            'blocked_tasks' => $blockedTasks,
            'ux_issues' => $uxIssues,
            'recent_feedback' => $events->where('event_name', 'feedback_submitted')->sortByDesc('created_at')->take(10)->values(),
            'session_insights' => [
                'average_actions_per_session' => $sessionGroups->isEmpty() ? null : round($sessionGroups->avg(fn (Collection $group) => $group->count()), 2),
                'average_session_duration_seconds' => $this->averageSessionDuration($sessionGroups),
            ],
        ];
    }

    private function buildFunnel(Collection $sessionGroups): array
    {
        $steps = [
            'board_created' => 'Board created',
            'card_created' => 'Card created',
            'card_moved' => 'Card moved',
            'card_completed' => 'Card completed',
        ];

        $counts = [];
        foreach ($steps as $eventName => $label) {
            $counts[] = [
                'event_name' => $eventName,
                'label' => $label,
                'count' => $sessionGroups->filter(fn (Collection $events) => $events->contains('event_name', $eventName))->count(),
            ];
        }

        $started = $counts[0]['count'] ?: 0;
        $completed = $counts[array_key_last($counts)]['count'] ?: 0;

        return [
            'steps' => $counts,
            'task_success_rate' => $started > 0 ? round(($completed / $started) * 100, 2) : null,
        ];
    }

    private function averageCycleTime(Collection $events): ?float
    {
        $grouped = $events
            ->filter(fn (UserEvent $event) => filled($event->payload['card_id'] ?? null))
            ->groupBy(fn (UserEvent $event) => $event->payload['card_id']);

        $durations = $grouped->map(function (Collection $cardEvents) {
            $created = $cardEvents->firstWhere('event_name', 'card_created');
            $completed = $cardEvents->first(fn (UserEvent $event) => in_array($event->event_name, ['card_completed', 'card_deleted'], true));

            if (! $created || ! $completed) {
                return null;
            }

            return $created->created_at->diffInMinutes($completed->created_at) / 60;
        })->filter();

        return $durations->isEmpty() ? null : round($durations->avg(), 2);
    }

    private function averageTimePerColumn(Collection $events): Collection
    {
        $movedEvents = $events
            ->filter(fn (UserEvent $event) => $event->event_name === 'card_moved' && filled($event->payload['card_id'] ?? null))
            ->groupBy(fn (UserEvent $event) => $event->payload['card_id']);

        $durations = collect();

        foreach ($movedEvents as $cardEvents) {
            $ordered = $cardEvents->sortBy('created_at')->values();

            for ($i = 1; $i < $ordered->count(); $i++) {
                $previous = $ordered[$i - 1];
                $current = $ordered[$i];
                $fromColumn = $previous->payload['to_column_id'] ?? $previous->payload['column_id'] ?? null;

                if (! $fromColumn) {
                    continue;
                }

                $durations->push([
                    'column_id' => (string) $fromColumn,
                    'hours' => $previous->created_at->diffInMinutes($current->created_at) / 60,
                ]);
            }
        }

        return $durations->groupBy('column_id')
            ->map(fn (Collection $group, string $columnId) => [
                'column_id' => $columnId,
                'average_hours' => round($group->avg('hours'), 2),
            ])
            ->values();
    }

    private function blockedTasks(Collection $events): Collection
    {
        $cardEvents = $events
            ->filter(fn (UserEvent $event) => filled($event->payload['card_id'] ?? null))
            ->groupBy(fn (UserEvent $event) => $event->payload['card_id']);

        return $cardEvents->map(function (Collection $group, string $cardId) {
            $latestMovement = $group
                ->filter(fn (UserEvent $event) => in_array($event->event_name, ['card_created', 'card_moved'], true))
                ->sortByDesc('created_at')
                ->first();

            $completed = $group->first(fn (UserEvent $event) => in_array($event->event_name, ['card_completed', 'card_deleted'], true));

            if (! $latestMovement || $completed || $latestMovement->created_at->greaterThan(now()->subHours(24))) {
                return null;
            }

            return [
                'card_id' => $cardId,
                'board_id' => $latestMovement->payload['board_id'] ?? null,
                'last_activity_at' => $latestMovement->created_at->toISOString(),
                'hours_blocked' => round($latestMovement->created_at->diffInMinutes(now()) / 60, 2),
            ];
        })->filter()->values();
    }

    private function averageSessionDuration(Collection $sessionGroups): ?float
    {
        $durations = $sessionGroups->map(function (Collection $events) {
            $start = $events->firstWhere('event_name', 'session_started');
            $end = $events->sortByDesc('created_at')->firstWhere('event_name', 'session_ended');

            if (! $start || ! $end) {
                return null;
            }

            return $start->created_at->diffInSeconds($end->created_at);
        })->filter();

        return $durations->isEmpty() ? null : round($durations->avg(), 2);
    }

    private function sanitizePayload(array $payload): array
    {
        $forbiddenKeys = [
            'password',
            'token',
            'secret',
            'email',
            'csrf',
            'csrf_token',
        ];

        $clean = [];

        foreach ($payload as $key => $value) {
            if (in_array(mb_strtolower((string) $key), $forbiddenKeys, true)) {
                continue;
            }

            if (is_array($value)) {
                $clean[$key] = $this->sanitizePayload($value);
                continue;
            }

            if (is_string($value)) {
                $clean[$key] = mb_substr($value, 0, 500);
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }

    private function normalizeTimestamp(CarbonInterface|string|null $value): Carbon
    {
        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        if (is_string($value) && $value !== '') {
            return Carbon::parse($value);
        }

        return now();
    }
}
