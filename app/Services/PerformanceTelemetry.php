<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

final class PerformanceTelemetry
{
    /** @param array{count: int, total_ms: float, slow_count: int} $queries */
    public function record(string $route, ?int $workspaceId, ?int $actorId, float $durationMs, int $status, array $queries): void
    {
        $scope = ['route' => $route, 'workspace_id' => $workspaceId, 'actor_id' => $actorId];
        $key = 'taskflow:performance:'.hash('sha256', json_encode($scope, JSON_THROW_ON_ERROR));
        $entry = Cache::get($key, ['scope' => $scope, 'samples' => []]);
        $entry['samples'][] = [
            'duration_ms' => round($durationMs, 2),
            'status' => $status,
            'queries' => $queries['count'],
            'query_ms' => $queries['total_ms'],
            'slow_queries' => $queries['slow_count'],
            'recorded_at' => now()->toIso8601String(),
        ];
        $entry['samples'] = array_slice($entry['samples'], -(int) config('taskflow.performance.sample_limit', 200));
        Cache::put($key, $entry, now()->addDay());

        $keys = Cache::get('taskflow:performance:registry', []);
        if (! in_array($key, $keys, true)) {
            $keys[] = $key;
            Cache::put('taskflow:performance:registry', $keys, now()->addDay());
        }
    }

    /** @return list<array{route: string, workspace_id: int|null, actor_id: int|null, samples: int, p50_ms: float, p95_ms: float, error_rate: float, average_queries: float}> */
    public function report(): array
    {
        return collect(Cache::get('taskflow:performance:registry', []))
            ->map(fn (string $key): ?array => Cache::get($key))
            ->filter()
            ->map(function (array $entry): array {
                $durations = collect($entry['samples'])->pluck('duration_ms')->map(fn ($value): float => (float) $value)->sort()->values()->all();
                $count = count($durations);
                $errors = collect($entry['samples'])->where('status', '>=', 500)->count();

                return [
                    ...$entry['scope'],
                    'samples' => $count,
                    'p50_ms' => $this->percentile($durations, 50),
                    'p95_ms' => $this->percentile($durations, 95),
                    'error_rate' => $count ? round(($errors / $count) * 100, 2) : 0.0,
                    'average_queries' => $count ? round((float) collect($entry['samples'])->avg('queries'), 2) : 0.0,
                ];
            })
            ->values()
            ->all();
    }

    /** @param list<float> $values */
    private function percentile(array $values, int $percentile): float
    {
        if ($values === []) {
            return 0.0;
        }

        $index = max(0, (int) ceil(($percentile / 100) * count($values)) - 1);

        return round($values[$index], 2);
    }
}
