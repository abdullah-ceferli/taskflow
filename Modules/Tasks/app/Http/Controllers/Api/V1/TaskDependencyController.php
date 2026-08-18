<?php

namespace Modules\Tasks\Http\Controllers\Api\V1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Tasks\Http\Requests\StoreTaskDependencyRequest;
use Modules\Tasks\Http\Resources\TaskResource;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskDependencyService;

final class TaskDependencyController
{
    use AuthorizesRequests;

    public function store(StoreTaskDependencyRequest $request, Task $task, TaskDependencyService $dependencies): TaskResource
    {
        $this->authorize('update', $task);
        $dependencies->add($task, $dependencies->task($request->integer('depends_on_task_id')), $request->user());

        return new TaskResource($task->load('dependencies'));
    }

    public function destroy(Task $task, Task $dependency, TaskDependencyService $dependencies)
    {
        $this->authorize('update', $task);
        $dependencies->remove($task, $dependency, request()->user());

        return response()->noContent();
    }
}
