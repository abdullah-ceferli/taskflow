<?php

namespace Modules\Tasks\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Tasks\Data\TaskFiltersData;
use Modules\Tasks\Models\Task;

interface TaskRepositoryInterface
{
    public function paginateFor(User $actor, TaskFiltersData $filters): LengthAwarePaginator;

    public function save(Task $task): Task;

    public function findForUpdate(int $taskId): Task;

    public function boardForProject(User $actor, int $projectId): Collection;

    public function delete(Task $task): void;

    public function filterProjectsFor(User $actor): Collection;

    public function filterUsersFor(User $actor): Collection;

    public function dependencyCandidates(User $actor, Task $task): Collection;
}
