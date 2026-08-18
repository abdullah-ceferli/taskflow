<?php

namespace Modules\Tasks\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Projects\Models\Project;
use Modules\Tasks\Services\TaskBoardService;

final class TaskBoardController
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Project $project, TaskBoardService $board): View
    {
        $this->authorize('view', $project);

        return view('tasks::board', ['project' => $project, 'columns' => $board->forProject($request->user(), $project->id)]);
    }
}
