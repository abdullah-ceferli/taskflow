<?php

namespace Modules\Tasks\Services;

use App\Exceptions\DomainRuleViolation;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Activity\Enums\ActivityEvent;
use Modules\Activity\Services\ActivityRecorder;
use Modules\Projects\Contracts\ProjectAccessInterface;
use Modules\Projects\Models\ProjectMilestone;
use Modules\Tasks\Data\CreateTaskData;
use Modules\Tasks\Data\UpdateTaskData;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Events\TaskCreated;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;

class TaskService
{
    public function __construct(private readonly TaskRepositoryInterface $tasks, private readonly ProjectAccessInterface $projects, private readonly ActivityRecorder $activity) {}

    public function create(User $actor, int $projectId, CreateTaskData $data): Task
    {
        $task = DB::transaction(function () use ($actor, $projectId, $data): Task {
            $access = $this->projects->forActor($projectId, $actor);
            if (! $access->active) {
                throw new DomainRuleViolation('Tasks can only be created in active projects.');
            }
            if (! $access->manager) {
                throw new DomainRuleViolation('The actor cannot create tasks in this project.');
            }
            if ($data->assigneeId && ! $this->projects->forActor($projectId, User::query()->findOrFail($data->assigneeId))->member) {
                throw new DomainRuleViolation('The assignee must be a project member.');
            }
            $this->validateMilestone($projectId, $data->milestoneId);

            $task = $this->tasks->save(new Task([
                'project_id' => $projectId, 'creator_id' => $actor->id, 'assignee_id' => $data->assigneeId,
                'title' => $data->title, 'description' => $data->description, 'status' => TaskStatus::Todo,
                'priority' => $data->priority, 'estimate_hours' => $data->estimateHours, 'milestone_id' => $data->milestoneId, 'due_at' => $data->dueAt,
            ]));
            $task->number = 'TSK-'.str_pad((string) $task->id, 6, '0', STR_PAD_LEFT);

            $task = $this->tasks->save($task);

            return $task;
        });

        TaskCreated::dispatch($actor, $task, ['project_id' => $projectId, 'task_id' => $task->id, 'task_number' => $task->number, 'task_title' => $task->title]);

        return $task;
    }

    public function update(Task $task, UpdateTaskData $data, User $actor): Task
    {
        return DB::transaction(function () use ($task, $data, $actor): Task {
            $this->validateMilestone($task->project_id, $data->milestoneId);
            $task->fill(['title' => $data->title, 'description' => $data->description, 'priority' => $data->priority, 'estimate_hours' => $data->estimateHours, 'milestone_id' => $data->milestoneId, 'due_at' => $data->dueAt]);
            $changed = array_keys($task->getDirty());
            $old = Arr::only($task->getRawOriginal(), $changed);
            $new = Arr::only($task->getAttributes(), $changed);
            $task = $this->tasks->save($task);
            if ($changed !== []) {
                $this->activity->record(ActivityEvent::TaskUpdated, $actor, $task, ['project_id' => $task->project_id, 'task_id' => $task->id, 'changed' => $changed, 'old' => $old, 'new' => $new]);
            }

            return $task;
        });
    }

    public function delete(Task $task, User $actor): void
    {
        DB::transaction(function () use ($task, $actor): void {
            $this->tasks->delete($task);
            $this->activity->record(ActivityEvent::TaskDeleted, $actor, $task, ['project_id' => $task->project_id, 'task_id' => $task->id]);
        });
    }

    private function validateMilestone(int $projectId, ?int $milestoneId): void
    {
        if ($milestoneId && ! ProjectMilestone::query()->whereKey($milestoneId)->where('project_id', $projectId)->exists()) {
            throw new DomainRuleViolation('The milestone must belong to the task project.');
        }
    }
}
