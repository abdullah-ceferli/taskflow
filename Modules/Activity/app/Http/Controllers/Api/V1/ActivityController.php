<?php

namespace Modules\Activity\Http\Controllers\Api\V1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Activity\Http\Requests\Api\V1\ActivityIndexRequest;
use Modules\Activity\Http\Resources\ActivityResource;
use Modules\Activity\Services\ActivityQueryService;
use Modules\Projects\Models\Project;
use Modules\Tasks\Models\Task;
use Spatie\Activitylog\Models\Activity;

final class ActivityController
{
    use AuthorizesRequests;

    public function __construct(private readonly ActivityQueryService $activity) {}

    public function index(ActivityIndexRequest $request)
    {
        $this->authorize('viewAny', Activity::class);

        return ActivityResource::collection($this->activity->paginate($request->user(), $request->validated()));
    }

    public function forProject(ActivityIndexRequest $request, Project $project)
    {
        $this->authorize('view', $project);

        return ActivityResource::collection($this->activity->paginate($request->user(), array_merge($request->validated(), ['project_id' => $project->id])));
    }

    public function forTask(ActivityIndexRequest $request, Task $task)
    {
        $this->authorize('view', $task);

        return ActivityResource::collection($this->activity->paginate($request->user(), array_merge($request->validated(), ['task_id' => $task->id])));
    }
}
