<?php

namespace App\Console\Commands;

use App\Services\PerformanceTelemetry;
use Illuminate\Console\Command;

final class PerformanceReport extends Command
{
    protected $signature = 'taskflow:performance:report';

    protected $description = 'Show scoped request latency, error-rate and query-count telemetry';

    public function handle(PerformanceTelemetry $telemetry): int
    {
        $rows = $telemetry->report();
        if ($rows === []) {
            $this->info('No performance samples are available in the current cache store.');

            return self::SUCCESS;
        }

        $this->table(['Route', 'Workspace', 'Actor', 'Samples', 'p50 ms', 'p95 ms', 'Error %', 'Avg queries'], array_map(
            fn (array $row): array => [$row['route'], $row['workspace_id'] ?? '-', $row['actor_id'] ?? '-', $row['samples'], $row['p50_ms'], $row['p95_ms'], $row['error_rate'], $row['average_queries']],
            $rows,
        ));

        return self::SUCCESS;
    }
}
