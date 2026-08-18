<?php

namespace Modules\Activity\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\CurrentWorkspace;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Projects\Models\Project;
use Modules\Tasks\Models\Task;
use Spatie\Activitylog\Models\Activity;

class ActivityQueryService
{
    /** @return Collection<int, Activity> */
    public function recentForProject(Project $project, int $limit = 5): Collection
    {
        return Activity::query()
            ->with('causer')
            ->where($this->scopeColumn('project_id'), $project->id)
            ->latest()
            ->take($limit)
            ->get();
    }

    /** @return Collection<int, Activity> */
    public function recentForTask(Task $task, int $limit = 5): Collection
    {
        return Activity::query()
            ->with('causer')
            ->where($this->scopeColumn('task_id'), $task->id)
            ->latest()
            ->take($limit)
            ->get();
    }

    /** @return Collection<int, Activity> */
    public function recentForUser(User $user, int $limit = 8): Collection
    {
        return $this->scopedQuery($user)->latest()->take($limit)->get();
    }

    public function paginate(User $user, array $filters = []): LengthAwarePaginator
    {
        $filters = $this->normaliseFilters($filters);

        return $this->scopedQuery($user)
            ->when($filters['event'] ?? null, fn (Builder $query, string $event) => $query->where('event', $event))
            ->when($filters['project_id'] ?? null, fn (Builder $query, int $id) => $query->where($this->scopeColumn('project_id'), $id))
            ->when($filters['task_id'] ?? null, fn (Builder $query, int $id) => $query->where($this->scopeColumn('task_id'), $id))
            ->when($filters['actor_id'] ?? null, fn (Builder $query, int $id) => $query->where('causer_id', $id))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    /** @return Collection<int, Activity> */
    public function exportForUser(User $user, int $limit = 10000): Collection
    {
        return $this->scopedQuery($user)->latest()->limit($limit)->get();
    }

    /** @return array{events: Collection<int, string>, projects: Collection<int, Project>, tasks: Collection<int, Task>, actors: Collection<int, User>} */
    public function filterOptions(User $user): array
    {
        $activities = $this->scopedQuery($user)->get();
        $projectIds = $activities->map(fn (Activity $activity) => $activity->properties->get('project_id'))->filter()->unique()->values();
        $taskIds = $activities->map(fn (Activity $activity) => $activity->properties->get('task_id'))->filter()->unique()->values();
        $actorIds = $activities->pluck('causer_id')->filter()->unique()->values();

        return [
            'events' => $activities->pluck('event')->filter()->unique()->sort()->values(),
            'projects' => Project::query()->whereIn('id', $projectIds)->orderBy('name')->get(),
            'tasks' => Task::query()->withTrashed()->whereIn('id', $taskIds)->orderBy('number')->get(),
            'actors' => User::query()->whereIn('id', $actorIds)->orderBy('name')->get(),
        ];
    }

    private function scopedQuery(User $user): Builder
    {
        $query = Activity::query()->with(['causer', 'subject']);

        if ($user->hasRole(UserRole::Admin->value)) {
            $workspaceId = app(CurrentWorkspace::class)->idFor($user);

            if (! $workspaceId) {
                return $query->whereRaw('1 = 0');
            }

            $legacyProjectIds = Project::query()->withTrashed()->where('workspace_id', $workspaceId)->select('id');

            return $query->where(fn (Builder $activities) => $activities
                ->where($this->scopeColumn('workspace_id'), $workspaceId)
                ->orWhere(fn (Builder $legacy) => $legacy
                    ->whereNull('properties->workspace_id')
                    ->whereIn($this->scopeColumn('project_id'), $legacyProjectIds)));
        }

        if ($user->hasRole(UserRole::ProjectManager->value)) {
            return $query->whereIn($this->scopeColumn('project_id'), Project::query()->manageableBy($user)->select('id'));
        }

        return $query->whereIn($this->scopeColumn('task_id'), Task::query()->visibleTo($user)->select('id'));
    }

    private function scopeColumn(string $property): string
    {
        return DB::connection()->getDriverName() === 'mysql' ? $property : "properties->{$property}";
    }

    private function normaliseFilters(array $filters): array
    {
        return [
            'event' => $filters['event'] ?? null,
            'project_id' => $filters['project_id'] ?? $filters['project'] ?? null,
            'task_id' => $filters['task_id'] ?? $filters['task'] ?? null,
            'actor_id' => $filters['actor_id'] ?? $filters['actor'] ?? null,
            'date_from' => $filters['date_from'] ?? $filters['from'] ?? null,
            'date_to' => $filters['date_to'] ?? $filters['to'] ?? null,
        ];
    }
}
