<?php

namespace Modules\Tasks\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Projects\Models\Project;
use Modules\Tasks\Data\CreateTaskData;
use Modules\Tasks\Data\TaskFiltersData;
use Modules\Tasks\Data\UpdateTaskData;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Http\Requests\Api\V1\AssignTaskRequest;
use Modules\Tasks\Http\Requests\Api\V1\ChangeTaskStatusRequest;
use Modules\Tasks\Http\Requests\Api\V1\StoreTaskRequest;
use Modules\Tasks\Http\Requests\Api\V1\TaskIndexRequest;
use Modules\Tasks\Http\Requests\Api\V1\UpdateTaskRequest;
use Modules\Tasks\Http\Resources\TaskResource;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;
use Modules\Tasks\Services\TaskAssignmentService;
use Modules\Tasks\Services\TaskService;
use Modules\Tasks\Services\TaskStatusService;

final class TaskController
{
    use AuthorizesRequests;

    public function __construct(private readonly TaskRepositoryInterface $tasks, private readonly TaskService $taskService, private readonly TaskAssignmentService $assignments, private readonly TaskStatusService $statuses) {}

    public function index(TaskIndexRequest $request)
    {
        $this->authorize('viewAny', Task::class);

        return TaskResource::collection($this->tasks->paginateFor($request->user(), TaskFiltersData::fromArray($request->validated())));
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $project = Project::query()->findOrFail($request->integer('project_id'));
        $this->authorize('create', [Task::class, $project]);
        $task = $this->taskService->create($request->user(), $project, CreateTaskData::fromArray($project->id, $request->validated()));

        return (new TaskResource($task))->response()->setStatusCode(201);
    }

    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $this->authorize('update', $task);

        return new TaskResource($this->taskService->update($task, UpdateTaskData::fromArray($request->validated()), $request->user()));
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $this->taskService->delete($task, actor: request()->user());

        return response()->noContent();
    }

    public function assign(AssignTaskRequest $request, Task $task): TaskResource
    {
        $this->authorize('assign', $task);
        $assignee = $request->filled('assignee_id') ? User::query()->findOrFail($request->integer('assignee_id')) : null;

        return new TaskResource($this->assignments->assign($task->load('project'), $assignee, $request->user()));
    }

    public function changeStatus(ChangeTaskStatusRequest $request, Task $task): TaskResource
    {
        $this->authorize('changeStatus', $task);

        return new TaskResource($this->statuses->change($task->load('project'), TaskStatus::from($request->string('status')->toString()), $request->user()));
    }
}
