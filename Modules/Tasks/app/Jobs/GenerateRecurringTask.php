<?php

namespace Modules\Tasks\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Tasks\Services\RecurringTaskService;

final class GenerateRecurringTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $recurringTaskId) {}

    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(RecurringTaskService $recurringTasks): void
    {
        $recurringTasks->generate($this->recurringTaskId);
    }
}
