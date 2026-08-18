<?php

namespace Modules\Tasks\Services;

use App\Exceptions\DomainRuleViolation;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Services\NotificationPreferenceService;
use Illuminate\Support\Facades\DB;
use Modules\Projects\Contracts\ProjectAccessInterface;
use Modules\Tasks\Events\TaskAssigned;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;

class TaskAssignmentService
{
    public function __construct(private readonly TaskRepositoryInterface $tasks, private readonly ProjectAccessInterface $projects, private readonly NotificationPreferenceService $preferences) {}

    public function assign(Task $task, ?User $assignee, User $actor): Task
    {
        if ($assignee && ! $this->projects->forActor($task->project_id, $assignee)->member) {
            throw new DomainRuleViolation('The assignee must belong to the project.');
        }

        $oldAssignee = $task->assignee;
        if ($oldAssignee?->id === $assignee?->id) {
            return $task;
        }

        $result = DB::transaction(function () use ($task, $assignee): Task {
            $task->assignee()->associate($assignee);

            return $this->tasks->save($task);
        });

        TaskAssigned::dispatch($actor, $result, [
            'project_id' => $result->project_id,
            'task_id' => $result->id,
            'old_assignee_id' => $oldAssignee?->id,
            'old_assignee_name' => $oldAssignee?->name ?: $oldAssignee?->email,
            'new_assignee_id' => $assignee?->id,
            'new_assignee_name' => $assignee?->name ?: $assignee?->email,
        ]);

        if ($assignee) {
            $workspaceId = (int) $result->project()->value('workspace_id');
            $channels = $this->preferences->channels($assignee, $workspaceId, 'task.assigned');
            if ($channels !== []) {
                $assignee->notify(new TaskAssignedNotification($result, $actor->id, $channels));
            }
        }

        return $result;
    }

    public function assignee(?int $userId): ?User
    {
        return $userId ? User::query()->findOrFail($userId) : null;
    }
}
