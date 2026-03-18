<?php

namespace App\Services;

use App\Models\Experiment;
use App\Models\ExperimentAssignment;
use App\Models\ExperimentEvent;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExperimentService
{
    private array $contextStack = [];

    public function beginExperiment(string $experimentKey, Authenticatable|User|null $user = null): bool
    {
        $this->contextStack[] = [
            'key' => $experimentKey,
            'variant' => $this->variant($experimentKey, $user),
        ];

        return true;
    }

    public function variantMatches(string $variantKey): bool
    {
        $context = end($this->contextStack) ?: null;

        return ($context['variant'] ?? 'A') === $variantKey;
    }

    public function endExperiment(): void
    {
        array_pop($this->contextStack);
    }

    public function variant(string $experimentKey, Authenticatable|User|null $user = null): string
    {
        $experiment = $this->activeExperiments()->firstWhere('key', $experimentKey);

        if (! $experiment) {
            return 'A';
        }

        if (! $user) {
            return $experiment->variants->sortBy('id')->first()?->key ?? 'A';
        }

        return $this->assignmentFor($user, $experiment)['variant_key'];
    }

    public function frontendAssignments(Authenticatable|User|null $user = null): array
    {
        if (! $user) {
            return [];
        }

        return $this->activeExperiments()
            ->mapWithKeys(function (Experiment $experiment) use ($user): array {
                $assignment = $this->assignmentFor($user, $experiment);

                return [
                    $experiment->key => [
                        'experiment_id' => $experiment->getKey(),
                        'variant_key' => $assignment['variant_key'],
                        'primary_metric' => $experiment->primary_metric,
                        'secondary_metrics' => $experiment->secondary_metrics ?? [],
                    ],
                ];
            })
            ->all();
    }

    public function recordEventBatch(Authenticatable|User|int|null $user, array $events): void
    {
        if ($events === []) {
            return;
        }

        $userId = $user instanceof Authenticatable || $user instanceof User
            ? $user->getAuthIdentifier()
            : $user;

        $rows = collect($events)
            ->flatMap(function (array $event) use ($userId): Collection {
                $assignments = collect((array) data_get($event, 'payload.experiments', []));
                $createdAt = $this->normalizeTimestamp($event['created_at'] ?? null);

                return $assignments
                    ->map(function (array|string $assignment, string $experimentKey) use ($event, $createdAt, $userId): ?array {
                        $experimentId = is_array($assignment) ? ($assignment['experiment_id'] ?? null) : null;
                        $variantKey = is_array($assignment) ? ($assignment['variant_key'] ?? null) : $assignment;

                        if (! $experimentId || ! $variantKey) {
                            $experiment = $this->activeExperiments()->firstWhere('key', $experimentKey);
                            $experimentId = $experiment?->getKey();
                        }

                        if (! $experimentId || ! $variantKey) {
                            return null;
                        }

                        return [
                            'user_id' => $userId,
                            'experiment_id' => $experimentId,
                            'variant_key' => (string) $variantKey,
                            'event_name' => (string) ($event['event_name'] ?? ''),
                            'metadata' => $this->sanitizeMetadata((array) ($event['payload'] ?? [])),
                            'created_at' => $createdAt,
                        ];
                    })
                    ->filter();
            })
            ->filter(fn (array $row) => $row['event_name'] !== '')
            ->values()
            ->all();

        if ($rows === []) {
            return;
        }

        ExperimentEvent::query()->insert($rows);
    }

    public function dashboardMetrics(): array
    {
        $experiments = Experiment::query()
            ->with(['variants', 'assignments', 'events'])
            ->orderByDesc('created_at')
            ->get();

        return [
            'summary' => [
                'total' => $experiments->count(),
                'running' => $experiments->where('status', 'running')->count(),
                'paused' => $experiments->where('status', 'paused')->count(),
                'completed' => $experiments->where('status', 'completed')->count(),
            ],
            'experiments' => $experiments->map(fn (Experiment $experiment) => $this->experimentMetrics($experiment))->values(),
        ];
    }

    public function winningVariant(Experiment $experiment): ?array
    {
        $metrics = $this->experimentMetrics($experiment);
        $variants = collect($metrics['variants']);

        if ($variants->isEmpty()) {
            return null;
        }

        return $variants
            ->sortByDesc('conversion_rate')
            ->sortByDesc('assignments')
            ->first();
    }

    private function experimentMetrics(Experiment $experiment): array
    {
        $primaryMetric = $experiment->primary_metric ?: 'card_created';
        $secondaryMetrics = collect($experiment->secondary_metrics ?? [])
            ->filter()
            ->values();

        $assignmentCounts = $experiment->assignments->groupBy('variant_key')->map->count();
        $eventGroups = $experiment->events->groupBy('variant_key');

        $variants = $experiment->variants
            ->sortBy('key')
            ->map(function ($variant) use ($assignmentCounts, $eventGroups, $primaryMetric, $secondaryMetrics) {
                $assignments = (int) ($assignmentCounts[$variant->key] ?? 0);
                $events = $eventGroups->get($variant->key, collect());
                $primaryCount = $events->where('event_name', $primaryMetric)->count();
                $engagementCount = $events->whereIn('event_name', $secondaryMetrics->all())->count();
                $conversionRate = $assignments > 0 ? round(($primaryCount / $assignments) * 100, 2) : null;
                $engagementRate = $assignments > 0 ? round(($engagementCount / $assignments) * 100, 2) : null;

                return [
                    'key' => $variant->key,
                    'weight' => $variant->weight,
                    'assignments' => $assignments,
                    'primary_events' => $primaryCount,
                    'engagement_events' => $engagementCount,
                    'conversion_rate' => $conversionRate,
                    'engagement_rate' => $engagementRate,
                    'drop_off_rate' => $conversionRate !== null ? round(100 - $conversionRate, 2) : null,
                    'secondary_metrics' => $secondaryMetrics->mapWithKeys(
                        fn (string $metric) => [$metric => $events->where('event_name', $metric)->count()]
                    )->all(),
                ];
            })
            ->values();

        $winner = $variants->sortByDesc('conversion_rate')->sortByDesc('assignments')->first();

        return [
            'id' => $experiment->getKey(),
            'key' => $experiment->key,
            'name' => $experiment->name,
            'status' => $experiment->status,
            'primary_metric' => $primaryMetric,
            'secondary_metrics' => $secondaryMetrics->all(),
            'start_at' => $experiment->start_at?->toISOString(),
            'end_at' => $experiment->end_at?->toISOString(),
            'winner' => $winner,
            'sample_size_reached' => $variants->sum('assignments') >= 100,
            'variants' => $variants->all(),
        ];
    }

    private function activeExperiments(): Collection
    {
        return Cache::remember('experiments.active', 60, function (): Collection {
            $now = now();

            return Experiment::query()
                ->with('variants')
                ->where('status', 'running')
                ->where(function ($query) use ($now) {
                    $query->whereNull('start_at')->orWhere('start_at', '<=', $now);
                })
                ->where(function ($query) use ($now) {
                    $query->whereNull('end_at')->orWhere('end_at', '>=', $now);
                })
                ->get();
        });
    }

    private function assignmentFor(Authenticatable|User $user, Experiment $experiment): array
    {
        $cacheKey = sprintf('experiments.assignments.user.%s.%s', $user->getAuthIdentifier(), $experiment->getKey());

        return Cache::rememberForever($cacheKey, function () use ($user, $experiment): array {
            $existing = ExperimentAssignment::query()
                ->where('user_id', $user->getAuthIdentifier())
                ->where('experiment_id', $experiment->getKey())
                ->first();

            if ($existing) {
                return [
                    'variant_key' => $existing->variant_key,
                ];
            }

            return DB::transaction(function () use ($user, $experiment): array {
                $existing = ExperimentAssignment::query()
                    ->where('user_id', $user->getAuthIdentifier())
                    ->where('experiment_id', $experiment->getKey())
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return [
                        'variant_key' => $existing->variant_key,
                    ];
                }

                $variantKey = $this->pickWeightedVariant($experiment);

                ExperimentAssignment::query()->create([
                    'user_id' => $user->getAuthIdentifier(),
                    'experiment_id' => $experiment->getKey(),
                    'variant_key' => $variantKey,
                ]);

                return [
                    'variant_key' => $variantKey,
                ];
            });
        });
    }

    private function pickWeightedVariant(Experiment $experiment): string
    {
        $variants = $experiment->variants->sortBy('id')->values();
        $totalWeight = max(1, (int) $variants->sum('weight'));
        $ticket = random_int(1, $totalWeight);
        $runningWeight = 0;

        foreach ($variants as $variant) {
            $runningWeight += max(1, (int) $variant->weight);

            if ($ticket <= $runningWeight) {
                return $variant->key;
            }
        }

        return $variants->first()?->key ?? 'A';
    }

    private function sanitizeMetadata(array $payload): array
    {
        unset($payload['experiments']);

        return collect($payload)
            ->map(function ($value) {
                if (is_array($value)) {
                    return $this->sanitizeMetadata($value);
                }

                if (is_string($value)) {
                    return mb_substr($value, 0, 500);
                }

                return $value;
            })
            ->all();
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
