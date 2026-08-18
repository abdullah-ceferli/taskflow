<?php

namespace Modules\Tasks\Repositories\Eloquent;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Projects\Enums\ProjectMemberRole;
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

        return $this->visibleTo($actor)->with(['project', 'creator', 'assignee'])->when(filled($filters->q), fn ($q) => $q->where(fn ($q) => $q->where('number', 'like', "%{$filters->q}%")->orWhere('title', 'like', "%{$filters->q}%")->orWhere('description', 'like', "%{$filters->q}%")))->when(TaskStatus::tryFrom((string) $filters->status), fn ($q, $v) => $q->where('status', $v->value))->when(TaskPriority::tryFrom((string) $filters->priority), fn ($q, $v) => $q->where('priority', $v->value))->when($filters->projectId, fn ($q, $id) => $q->where('project_id', $id))->when($filters->assigneeId, fn ($q, $id) => $q->where('assignee_id', $id))->when($filters->dueBefore, fn ($q, $d) => $q->whereDate('due_at', '<=', $d))->orderBy($sort, $direction)->paginate($filters->perPage)->withQueryString();
    }

    public function save(Task $task): Task
    {
        $task->save();

        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    public function filterProjectsFor(User $actor): Collection
    {
        return Project::query()->whereIn('id', $this->visibleTo($actor)->select('project_id'))->orderBy('name')->get();
    }

    public function filterUsersFor(User $actor): Collection
    {
        return User::query()->whereIn('id', $this->visibleTo($actor)->whereNotNull('assignee_id')->select('assignee_id'))->orderBy('name')->get();
    }

    private function visibleTo(User $actor)
    {
        if ($actor->hasRole(UserRole::Admin->value)) {
            return Task::query();
        } if ($actor->hasRole(UserRole::ProjectManager->value)) {
            return Task::query()->whereHas('project', fn ($p) => $p->where('owner_id', $actor->id)->orWhereHas('memberships', fn ($m) => $m->where('user_id', $actor->id)->where('member_role', ProjectMemberRole::Manager->value)));
        }

        return Task::query()->where('assignee_id', $actor->id);
    }
}
