<?php

namespace Modules\Tasks\Http\Controllers\Api\V1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Tasks\Http\Requests\Api\V1\StoreTaskLabelRequest;
use Modules\Tasks\Http\Requests\Api\V1\SyncTaskLabelsRequest;
use Modules\Tasks\Http\Resources\TaskLabelResource;
use Modules\Tasks\Http\Resources\TaskResource;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskLabelService;

final class TaskLabelController
{
    use AuthorizesRequests;

    public function index(TaskLabelService $labels)
    {
        return TaskLabelResource::collection($labels->list(request()->user()));
    }

    public function store(StoreTaskLabelRequest $request, TaskLabelService $labels): JsonResponse
    {
        $label = $labels->create($request->user(), $request->string('name')->toString(), $request->string('color')->toString(), $request->integer('project_id') ?: null);

        return (new TaskLabelResource($label))->response()->setStatusCode(201);
    }

    public function sync(SyncTaskLabelsRequest $request, Task $task, TaskLabelService $labels): TaskResource
    {
        $this->authorize('update', $task);

        return new TaskResource($labels->sync($task, $request->user(), $request->validated('label_ids')));
    }
}
