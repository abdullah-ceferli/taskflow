<?php

namespace Modules\Tasks\Console\Commands;

use Illuminate\Console\Command;
use Modules\Tasks\Jobs\GenerateRecurringTask;
use Modules\Tasks\Models\RecurringTask;

final class DispatchRecurringTasks extends Command
{
    protected $signature = 'taskflow:tasks:dispatch-recurring';

    protected $description = 'Queue due recurring task definitions for idempotent generation';

    public function handle(): int
    {
        $count = 0;
        RecurringTask::query()->where('active', true)->where('next_run_at', '<=', now())->orderBy('id')->pluck('id')->each(function (int $id) use (&$count): void {
            GenerateRecurringTask::dispatch($id);
            $count++;
        });
        $this->info("Queued {$count} recurring task definitions.");

        return self::SUCCESS;
    }
}
