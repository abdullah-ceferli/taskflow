<?php

namespace Modules\Tasks\Services;

use App\Exceptions\DomainRuleViolation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Activity\Enums\ActivityEvent;
use Modules\Activity\Services\ActivityRecorder;
use Modules\Projects\Contracts\ProjectAccessInterface;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskDependency;

final class TaskDependencyService
{
    public function __construct(private readonly ProjectAccessInterface $projects, private readonly ActivityRecorder $activity) {}

    public function add(Task $task, Task $dependency, User $actor): TaskDependency
    {
        if (! $this->projects->forActor($task->project_id, $actor)->manager) {
            throw new DomainRuleViolation('Only a project manager may change task dependencies.');
        }

        return DB::transaction(function () use ($task, $dependency, $actor): TaskDependency {
            Task::query()->where('project_id', $task->project_id)->lockForUpdate()->pluck('id');
            if ($task->id === $dependency->id || $task->project_id !== $dependency->project_id) {
                throw new DomainRuleViolation('A dependency must be another task in the same project.');
            }
            if ($this->wouldCreateCycle($task->id, $dependency->id)) {
                throw new DomainRuleViolation('This dependency would create a cycle.');
            }

            $edge = TaskDependency::query()->firstOrCreate(['task_id' => $task->id, 'depends_on_task_id' => $dependency->id]);
            if ($edge->wasRecentlyCreated) {
                $this->activity->record(ActivityEvent::TaskDependencyAdded, $actor, $task, ['project_id' => $task->project_id, 'task_id' => $task->id, 'depends_on_task_id' => $dependency->id]);
            }

            return $edge;
        });
    }

    public function task(int $taskId): Task
    {
        return Task::query()->findOrFail($taskId);
    }

    public function remove(Task $task, Task $dependency, User $actor): void
    {
        if (! $this->projects->forActor($task->project_id, $actor)->manager || $task->project_id !== $dependency->project_id) {
            throw new DomainRuleViolation('The dependency cannot be removed.');
        }

        DB::transaction(function () use ($task, $dependency, $actor): void {
            Task::query()->where('project_id', $task->project_id)->lockForUpdate()->pluck('id');
            if (TaskDependency::query()->where('task_id', $task->id)->where('depends_on_task_id', $dependency->id)->delete()) {
                $this->activity->record(ActivityEvent::TaskDependencyRemoved, $actor, $task, ['project_id' => $task->project_id, 'task_id' => $task->id, 'depends_on_task_id' => $dependency->id]);
            }
        });
    }

    public function isBlocked(Task $task): bool
    {
        return $task->dependencies()->where('status', '!=', TaskStatus::Done->value)->exists();
    }

    private function wouldCreateCycle(int $taskId, int $dependencyId): bool
    {
        $frontier = [$dependencyId];
        $visited = [];

        while ($frontier !== []) {
            $current = array_pop($frontier);
            if ($current === $taskId) {
                return true;
            }
            if (isset($visited[$current])) {
                continue;
            }
            $visited[$current] = true;
            $frontier = [...$frontier, ...TaskDependency::query()->where('task_id', $current)->pluck('depends_on_task_id')->all()];
        }

        return false;
    }
}
