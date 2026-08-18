<?php

namespace Modules\Tasks\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Modules\Tasks\Http\Requests\StoreTaskDependencyRequest;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskDependencyService;

final class TaskDependencyController
{
    use AuthorizesRequests;

    public function store(StoreTaskDependencyRequest $request, Task $task, TaskDependencyService $dependencies): RedirectResponse
    {
        $this->authorize('update', $task);
        $dependencies->add($task, $dependencies->task($request->integer('depends_on_task_id')), $request->user());

        return back()->with('success', 'Dependency added.');
    }

    public function destroy(Task $task, Task $dependency, TaskDependencyService $dependencies): RedirectResponse
    {
        $this->authorize('update', $task);
        $dependencies->remove($task, $dependency, request()->user());

        return back()->with('success', 'Dependency removed.');
    }
}
