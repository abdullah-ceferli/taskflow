<?php

namespace App\Console\Commands;

use App\Notifications\TaskDueSoonNotification;
use App\Services\NotificationDeliveryService;
use App\Services\NotificationPreferenceService;
use Illuminate\Console\Command;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Models\Task;

final class NotifyDueSoonTasks extends Command
{
    protected $signature = 'taskflow:tasks:notify-due-soon';

    protected $description = 'Queue one due-soon notification per assignee and task each day';

    public function handle(NotificationPreferenceService $preferences, NotificationDeliveryService $deliveries): int
    {
        $queued = 0;
        Task::query()->with(['assignee', 'project'])
            ->whereNotNull('assignee_id')
            ->whereDate('due_at', today()->addDay())
            ->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Cancelled->value])
            ->each(function (Task $task) use ($preferences, $deliveries, &$queued): void {
                $channels = $preferences->channels($task->assignee, $task->project->workspace_id, 'task.due_soon');
                if ($deliveries->sendOnce(
                    $task->assignee,
                    $task->project->workspace_id,
                    'task.due_soon',
                    $task->getMorphClass(),
                    $task->id,
                    new TaskDueSoonNotification($task, $channels),
                    $channels,
                )) {
                    $queued++;
                }
            });

        $this->info("Queued {$queued} due-soon notifications.");

        return self::SUCCESS;
    }
}
