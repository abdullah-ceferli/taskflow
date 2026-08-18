<?php

namespace Modules\Projects\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Modules\Projects\Http\Requests\StoreProjectMilestoneRequest;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectMilestone;
use Modules\Projects\Services\ProjectMilestoneService;

final class ProjectMilestoneController
{
    use AuthorizesRequests;

    public function store(StoreProjectMilestoneRequest $request, Project $project, ProjectMilestoneService $milestones): RedirectResponse
    {
        $this->authorize('update', $project);
        $milestones->create($project, $request->user(), $request->string('name')->toString(), $request->string('description')->toString() ?: null, $request->string('due_at')->toString());

        return back()->with('success', 'Milestone created.');
    }

    public function complete(Project $project, ProjectMilestone $milestone, ProjectMilestoneService $milestones): RedirectResponse
    {
        abort_unless($milestone->project_id === $project->id, 404);
        $this->authorize('update', $project);
        $milestones->complete($milestone, request()->user());

        return back()->with('success', 'Milestone completed.');
    }
}
