<?php

namespace App\Console\Commands;

use App\Services\ExpiredReportCleanupService;
use Illuminate\Console\Command;

final class PruneExpiredReportExports extends Command
{
    protected $signature = 'taskflow:reports:prune-expired';

    protected $description = 'Delete expired report files and their metadata';

    public function handle(ExpiredReportCleanupService $cleanup): int
    {
        $count = $cleanup->prune();
        $this->info("Pruned {$count} expired report exports.");

        return self::SUCCESS;
    }
}
