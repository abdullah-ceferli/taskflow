<?php

namespace App\Services;

use Illuminate\Database\Events\QueryExecuted;

final class QueryTelemetry
{
    private int $count = 0;

    private float $totalMs = 0;

    private int $slowCount = 0;

    public function reset(): void
    {
        $this->count = 0;
        $this->totalMs = 0;
        $this->slowCount = 0;
    }

    public function record(QueryExecuted $query): void
    {
        $this->count++;
        $this->totalMs += $query->time;
        if ($query->time >= (float) config('taskflow.performance.slow_query_ms', 250)) {
            $this->slowCount++;
        }
    }

    /** @return array{count: int, total_ms: float, slow_count: int} */
    public function snapshot(): array
    {
        return ['count' => $this->count, 'total_ms' => round($this->totalMs, 2), 'slow_count' => $this->slowCount];
    }
}
