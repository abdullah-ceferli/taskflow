<?php

namespace App\Console\Commands;

use App\Jobs\ArchiveActivityLog as ArchiveActivityLogJob;
use Illuminate\Console\Command;

final class ArchiveActivityLog extends Command
{
    protected $signature = 'taskflow:activity:archive {--before= : Archive records older than this ISO date}';

    protected $description = 'Queue private activity-log archival and retention cleanup';

    public function handle(): int
    {
        $before = $this->option('before')
            ? now()->parse((string) $this->option('before'))->startOfDay()
            : now()->subDays((int) config('taskflow.activity_retention.days', 365))->startOfDay();

        ArchiveActivityLogJob::dispatch($before->toDateTimeImmutable());
        $this->info('Activity archive job queued for records before '.$before->toDateString().'.');

        return self::SUCCESS;
    }
}
