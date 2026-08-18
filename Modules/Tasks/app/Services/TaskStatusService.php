<?php

namespace Modules\Tasks\Services;

use App\Enums\PermissionName;
use App\Exceptions\OptimisticLockConflict;
use App\Models\User;
use App\Notifications\TaskStatusChangedNotification;
use App\Services\NotificationPreferenceService;
use Illuminate\Support\Facades\DB;
use Modules\Activity\Enums\ActivityEvent;
use Modules\Activity\Services\ActivityRecorder;
use Modules\Projects\Contracts\ProjectAccessInterface;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Events\TaskStatusChanged;
use Modules\Tasks\Exceptions\InvalidTaskStatusTransition;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;

class TaskStatusService
{
    public function __construct(private readonly TaskRepositoryInterface $tasks, private readonly ProjectAccessInterface $projects, private readonly NotificationPreferenceService $preferences, private readonly TaskDependencyService $dependencies, private readonly ActivityRecorder $activity) {}

    /** @return list<TaskStatus> */
    public function availableStatuses(Task $task, User $actor): array
    {
        $map = [TaskStatus::Todo->value => [TaskStatus::InProgress, TaskStatus::Cancelled], TaskStatus::InProgress->value => [TaskStatus::Todo, TaskStatus::Review, TaskStatus::Cancelled], TaskStatus::Review->value => [TaskStatus::InProgress, TaskStatus::Done]];
        $next = $map[$task->status->value] ?? [];
        if (in_array($task->status, [TaskStatus::Done, TaskStatus::Cancelled], true) && $this->mayReopen($task, $actor)) {
            $next = [$task->status === TaskStatus::Done ? TaskStatus::InProgress : TaskStatus::Todo];
        }

        if ($this->dependencies->isBlocked($task)) {
            $next = array_values(array_filter($next, fn (TaskStatus $status) => ! in_array($status, [TaskStatus::InProgress, TaskStatus::Review, TaskStatus::Done], true)));
        }

        return $next;
    }

    public function change(Task $task, TaskStatus $to, User $actor, ?string $expectedUpdatedAt = null): Task
    {
        try {
            [$task, $from] = DB::transaction(function () use ($task, $to, $actor, $expectedUpdatedAt): array {
                $task = $this->tasks->findForUpdate($task->id);
                if ($expectedUpdatedAt && $task->updated_at?->toISOString() !== $expectedUpdatedAt) {
                    throw new OptimisticLockConflict('The task changed after the board was loaded. Refresh and try again.');
                }
                if (! in_array($to, $this->availableStatuses($task, $actor), true)) {
                    throw new InvalidTaskStatusTransition('This task status transition is not allowed or the task is blocked.');
                }

                $from = $task->status->value;
                if ($to === TaskStatus::InProgress && $task->started_at === null) {
                    $task->started_at = now();
                }
                if ($to === TaskStatus::Done) {
                    $task->completed_at = now();
                }
                if ($task->status === TaskStatus::Done && $to !== TaskStatus::Done) {
                    $task->completed_at = null;
                }
                $task->status = $to;
                $task = $this->tasks->save($task);

                return [$task, $from];
            });
        } catch (OptimisticLockConflict $exception) {
            $this->activity->record(ActivityEvent::TaskBoardConflict, $actor, $task, ['project_id' => $task->project_id, 'task_id' => $task->id, 'expected_updated_at' => $expectedUpdatedAt, 'actual_updated_at' => $task->fresh()->updated_at?->toISOString()]);

            throw $exception;
        }

        TaskStatusChanged::dispatch($actor, $task, ['project_id' => $task->project_id, 'task_id' => $task->id, 'old' => $from, 'new' => $to->value]);

        if ($task->assignee && $task->assignee_id !== $actor->id) {
            $workspaceId = (int) $task->project()->value('workspace_id');
            $channels = $this->preferences->channels($task->assignee, $workspaceId, 'task.status_changed');
            if ($channels !== []) {
                $task->assignee->notify(new TaskStatusChangedNotification($task, $from, $to->value, $channels));
            }
        }

        return $task;
    }

    private function mayReopen(Task $task, User $actor): bool
    {
        return $actor->hasPermissionTo(PermissionName::TasksStatusChange->value) && $this->projects->forActor($task->project_id, $actor)->manager;
    }
}
