<?php

namespace Modules\Projects\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\Projects\Models\Project;
use Modules\Projects\Http\Requests\Api\V1\StoreProjectApiRequest;
use Modules\Projects\Http\Resources\ProjectMemberResource;
use Modules\Projects\Services\ProjectMemberService;
use Modules\Projects\Repositories\Contracts\ProjectMemberRepositoryInterface;
use Modules\Projects\Data\AddProjectMemberData;
use Illuminate\Http\Response;

class ProjectMemberController extends Controller
{
    public function __construct(
        private ProjectMemberService $memberService,
        private ProjectMemberRepositoryInterface $memberRepository,
    ) {}

    /**
     * List project members
     */
    public function index(Project $project)
    {
        $this->authorize('view', $project);
        
        $members = $this->memberRepository->getProjectMembers($project->id, 20);
        return ProjectMemberResource::collection($members);
    }

    /**
     * Add member to project
     */
    public function store(StoreProjectApiRequest $request, Project $project)
    {
        $this->authorize('manageMember', $project);

        $data = new AddProjectMemberData(
            projectId: $project->id,
            userId: $request->validated('user_id'),
            memberRole: $request->validated('member_role') ?? 'member',
        );

        $member = $this->memberService->addMember($data);

        return new ProjectMemberResource($member), Response::HTTP_CREATED;
    }

    /**
     * Remove member from project
     */
    public function destroy(Project $project, int $userId)
    {
        $this->authorize('manageMember', $project);

        $this->memberService->removeMember($project->id, $userId);

        return response()->noContent();
    }
}
