<?php

namespace Modules\Projects\Repositories\Eloquent;

use Modules\Projects\Models\Project;
use Modules\Projects\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Project::query()
            ->with(['owner', 'members'])
            ->latest()
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Project
    {
        return Project::with(['owner', 'members'])
            ->findOrFail($id);
    }

    public function getByOwnerId(int $ownerId, int $perPage = 15): LengthAwarePaginator
    {
        return Project::query()
            ->where('owner_id', $ownerId)
            ->with(['members'])
            ->latest()
            ->paginate($perPage);
    }

    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return Project::query()
            ->where('status', 'active')
            ->with(['owner', 'members'])
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $attributes): Project
    {
        return Project::query()->create($attributes);
    }

    public function update(Project $project, array $attributes): Project
    {
        $project->update($attributes);
        return $project;
    }

    public function delete(Project $project): bool
    {
        return $project->delete();
    }

    public function userHasAccess(Project $project, int $userId): bool
    {
        // User has access if they are owner OR a member
        return $project->owner_id === $userId || $project->hasMember($project->owner()->find($userId));
    }
}
