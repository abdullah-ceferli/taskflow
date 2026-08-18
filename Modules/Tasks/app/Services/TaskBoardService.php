<?php

namespace Modules\Tasks\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;

final class TaskBoardService
{
    public function __construct(private readonly TaskRepositoryInterface $tasks) {}

    /** @return Collection<string, array{status: TaskStatus, tasks: Collection}> */
    public function forProject(User $actor, int $projectId): Collection
    {
        $tasks = $this->tasks->boardForProject($actor, $projectId)->groupBy(fn ($task) => $task->status->value);

        return collect(TaskStatus::cases())->mapWithKeys(fn (TaskStatus $status) => [
            $status->value => ['status' => $status, 'tasks' => $tasks->get($status->value, collect())],
        ]);
    }
}
