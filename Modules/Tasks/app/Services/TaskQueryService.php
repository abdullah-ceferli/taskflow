<?php

namespace Modules\Tasks\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Tasks\Data\TaskFiltersData;
use Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;

final class TaskQueryService
{
    public function __construct(private readonly TaskRepositoryInterface $tasks) {}

    public function paginate(User $actor, TaskFiltersData $filters): LengthAwarePaginator
    {
        return $this->tasks->paginateFor($actor, $filters);
    }

    public function projects(User $actor): Collection
    {
        return $this->tasks->filterProjectsFor($actor);
    }

    public function users(User $actor): Collection
    {
        return $this->tasks->filterUsersFor($actor);
    }
}
