<?php

namespace App\Console\Commands;

use App\Services\OperationalHealthService;
use App\Services\OperationalMetricsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class CheckOperationalHealth extends Command
{
    protected $signature = 'taskflow:operations:check';

    protected $description = 'Evaluate readiness and TaskFlow SLO thresholds';

    public function handle(OperationalHealthService $health, OperationalMetricsService $metrics): int
    {
        $readiness = $health->readiness();
        $snapshot = $metrics->snapshot();
        $thresholds = (array) config('taskflow.operations.thresholds', []);
        $breaches = [];

        foreach ($thresholds as $metric => $limit) {
            if (isset($snapshot[$metric]) && $snapshot[$metric] > $limit) {
                $breaches[$metric] = ['value' => $snapshot[$metric], 'limit' => $limit];
            }
        }

        if (! $readiness['healthy'] || $breaches !== []) {
            Log::channel('structured')->error('taskflow.slo_breach', [
                'readiness' => $readiness['components'],
                'breaches' => $breaches,
                'owner' => config('taskflow.operations.owner'),
                'runbook' => config('taskflow.operations.runbook_url'),
            ]);
            $this->error('Operational check failed. Inspect the structured taskflow.slo_breach event.');

            return self::FAILURE;
        }

        $this->info('Operational readiness and SLO thresholds passed.');

        return self::SUCCESS;
    }
}
