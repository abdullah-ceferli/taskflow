<?php

namespace Modules\Projects\Http\Controllers\Api\V1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Projects\Http\Requests\StoreProjectMilestoneRequest;
use Modules\Projects\Http\Resources\ProjectMilestoneResource;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectMilestone;
use Modules\Projects\Services\ProjectMilestoneService;

final class ProjectMilestoneController
{
    use AuthorizesRequests;

    public function index(Project $project, ProjectMilestoneService $milestones)
    {
        $this->authorize('view', $project);

        return ProjectMilestoneResource::collection($milestones->forProject($project));
    }

    public function store(StoreProjectMilestoneRequest $request, Project $project, ProjectMilestoneService $milestones): JsonResponse
    {
        $this->authorize('update', $project);
        $milestone = $milestones->create($project, $request->user(), $request->string('name')->toString(), $request->string('description')->toString() ?: null, $request->string('due_at')->toString());

        return (new ProjectMilestoneResource($milestone))->response()->setStatusCode(201);
    }

    public function complete(Project $project, ProjectMilestone $milestone, ProjectMilestoneService $milestones): ProjectMilestoneResource
    {
        abort_unless($milestone->project_id === $project->id, 404);
        $this->authorize('update', $project);

        return new ProjectMilestoneResource($milestones->complete($milestone, request()->user()));
    }
}
