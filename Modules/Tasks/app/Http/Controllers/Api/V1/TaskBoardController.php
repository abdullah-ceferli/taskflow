<?php

namespace Modules\Tasks\Http\Controllers\Api\V1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\Projects\Models\Project;
use Modules\Tasks\Http\Resources\TaskBoardResource;
use Modules\Tasks\Services\TaskBoardService;

final class TaskBoardController
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Project $project, TaskBoardService $board): TaskBoardResource
    {
        $this->authorize('view', $project);

        return new TaskBoardResource($board->forProject($request->user(), $project->id));
    }
}
