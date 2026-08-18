<?php

namespace Modules\Tasks\Http\Controllers\Api\V1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Projects\Models\Project;
use Modules\Tasks\Data\CreateRecurringTaskData;
use Modules\Tasks\Http\Requests\StoreRecurringTaskRequest;
use Modules\Tasks\Http\Resources\RecurringTaskResource;
use Modules\Tasks\Models\RecurringTask;
use Modules\Tasks\Services\RecurringTaskService;

final class RecurringTaskController
{
    use AuthorizesRequests;

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        return RecurringTaskResource::collection($project->recurringTasks()->latest()->get());
    }

    public function store(StoreRecurringTaskRequest $request, Project $project, RecurringTaskService $service): JsonResponse
    {
        $this->authorize('update', $project);

        return (new RecurringTaskResource($service->create($project, $request->user(), CreateRecurringTaskData::fromArray($request->validated()))))->response()->setStatusCode(201);
    }

    public function destroy(Project $project, RecurringTask $recurringTask, RecurringTaskService $service)
    {
        abort_unless($recurringTask->project_id === $project->id, 404);
        $this->authorize('update', $project);
        $service->deactivate($recurringTask, request()->user());

        return response()->noContent();
    }
}
