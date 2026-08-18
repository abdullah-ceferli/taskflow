<?php

namespace Modules\Projects\Http\Controllers\Api\V1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Projects\Data\CreateProjectData;
use Modules\Projects\Data\UpdateProjectData;
use Modules\Projects\Enums\ProjectMemberRole;
use Modules\Projects\Http\Requests\Api\V1\ProjectIndexRequest;
use Modules\Projects\Http\Requests\Api\V1\StoreProjectMemberRequest;
use Modules\Projects\Http\Requests\Api\V1\StoreProjectRequest;
use Modules\Projects\Http\Requests\Api\V1\UpdateProjectRequest;
use Modules\Projects\Http\Resources\ProjectMemberResource;
use Modules\Projects\Http\Resources\ProjectResource;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectMember;
use Modules\Projects\Repositories\Contracts\ProjectRepositoryInterface;
use Modules\Projects\Services\ProjectMemberService;
use Modules\Projects\Services\ProjectService;

final class ProjectController
{
    use AuthorizesRequests;

    public function __construct(private readonly ProjectRepositoryInterface $projects, private readonly ProjectService $projectService, private readonly ProjectMemberService $members) {}

    public function index(ProjectIndexRequest $request)
    {
        $this->authorize('viewAny', Project::class);

        return ProjectResource::collection($this->projects->paginateFor($request->user(), $request->string('search')->trim()->toString(), $request->string('status')->toString()));
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);
        $project = $this->projectService->create($request->user(), CreateProjectData::fromArray($request->validated()));

        return (new ProjectResource($project))->response()->setStatusCode(201);
    }

    public function show(Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        return new ProjectResource($project->load('milestones'));
    }

    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        $this->authorize('update', $project);

        return new ProjectResource($this->projectService->update($project, UpdateProjectData::fromArray($request->validated()), $request->user()));
    }

    public function destroy(Project $project)
    {
        $this->authorize('archive', $project);
        $this->projectService->archive($project, request()->user());

        return response()->noContent();
    }

    public function members(Project $project)
    {
        $this->authorize('view', $project);

        return ProjectMemberResource::collection($this->members->memberships($project));
    }

    public function storeMember(StoreProjectMemberRequest $request, Project $project): JsonResponse
    {
        $this->authorize('manageMembers', $project);
        $user = $this->members->user($request->integer('user_id'));
        $member = $this->members->addMember($project, $user, ProjectMemberRole::from($request->string('member_role')->toString()), actor: $request->user());

        return (new ProjectMemberResource($member))->response()->setStatusCode(201);
    }

    public function destroyMember(Project $project, ProjectMember $member)
    {
        abort_unless($member->project_id === $project->id, 404);
        $this->authorize('manageMembers', $project);
        $this->members->removeMember($project, $member->user, actor: request()->user());

        return response()->noContent();
    }
}
