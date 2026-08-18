<?php

namespace Modules\Tasks\Repositories\Eloquent;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Projects\Models\Project;
use Modules\Tasks\Data\TaskFiltersData;
use Modules\Tasks\Enums\TaskPriority;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function paginateFor(User $actor, TaskFiltersData $filters): LengthAwarePaginator
    {
        $columns = ['created_at', 'due_at', 'priority', 'status', 'number'];
        $sort = in_array($filters->sort, $columns, true) ? $filters->sort : 'created_at';
        $direction = $filters->direction === 'asc' ? 'asc' : 'desc';

        return Task::query()->visibleTo($actor)->with(['project', 'creator', 'assignee', 'labels'])->when(filled($filters->q), fn ($q) => $q->where(fn ($q) => $q->where('number', 'like', "%{$filters->q}%")->orWhere('title', 'like', "%{$filters->q}%")->orWhere('description', 'like', "%{$filters->q}%")))->when(TaskStatus::tryFrom((string) $filters->status), fn ($q, $v) => $q->where('status', $v->value))->when(TaskPriority::tryFrom((string) $filters->priority), fn ($q, $v) => $q->where('priority', $v->value))->when($filters->projectId, fn ($q, $id) => $q->where('project_id', $id))->when($filters->assigneeId, fn ($q, $id) => $q->where('assignee_id', $id))->when($filters->labelIds !== [], fn ($q) => $q->whereHas('labels', fn ($labels) => $labels->whereIn('task_labels.id', $filters->labelIds)))->when($filters->dueBefore, fn ($q, $d) => $q->whereDate('due_at', '<=', $d))->orderBy($sort, $direction)->paginate($filters->perPage)->withQueryString();
    }

    public function save(Task $task): Task
    {
        $task->save();

        return $task;
    }

    public function findForUpdate(int $taskId): Task
    {
        return Task::query()->with(['project', 'assignee'])->lockForUpdate()->findOrFail($taskId);
    }

    public function boardForProject(User $actor, int $projectId): Collection
    {
        return Task::query()
            ->visibleTo($actor)
            ->where('project_id', $projectId)
            ->with(['assignee', 'labels', 'dependencies'])
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->orderBy('id')
            ->get();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    public function filterProjectsFor(User $actor): Collection
    {
        return Project::query()->whereIn('id', Task::query()->visibleTo($actor)->select('project_id'))->orderBy('name')->get();
    }

    public function filterUsersFor(User $actor): Collection
    {
        return User::query()->whereIn('id', Task::query()->visibleTo($actor)->whereNotNull('assignee_id')->select('assignee_id'))->orderBy('name')->get();
    }

    public function dependencyCandidates(User $actor, Task $task): Collection
    {
        return Task::query()
            ->visibleTo($actor)
            ->where('project_id', $task->project_id)
            ->whereKeyNot($task->id)
            ->orderBy('number')
            ->get();
    }
}
