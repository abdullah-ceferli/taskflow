<?php

namespace Modules\Projects\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Modules\Projects\Models\Project;
use Modules\Projects\Http\Requests\Web\AddProjectMemberRequest;
use Modules\Projects\Services\ProjectMemberService;
use Modules\Projects\Repositories\Contracts\ProjectMemberRepositoryInterface;
use Modules\Projects\Data\AddProjectMemberData;

class ProjectMemberController extends Controller
{
    public function __construct(
        private ProjectMemberService $memberService,
        private ProjectMemberRepositoryInterface $memberRepository,
    ) {}

    /**
     * Display project members
     */
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $members = $this->memberRepository->getProjectMembers($project->id, 15);
        return view('projects::members.index', [
            'project' => $project,
            'members' => $members,
        ]);
    }

    /**
     * Show add member form
     */
    public function create(Project $project)
    {
        $this->authorize('manageMember', $project);
        return view('projects::members.create', ['project' => $project]);
    }

    /**
     * Add member to project
     */
    public function store(AddProjectMemberRequest $request, Project $project)
    {
        $this->authorize('manageMember', $project);

        $data = new AddProjectMemberData(
            projectId: $project->id,
            userId: $request->validated('user_id'),
            memberRole: $request->validated('member_role'),
        );

        $this->memberService->addMember($data);

        return redirect()->route('projects.members.index', $project)->with('success', 'Member added successfully.');
    }

    /**
     * Remove member from project
     */
    public function destroy(Project $project, int $userId)
    {
        $this->authorize('manageMember', $project);

        $this->memberService->removeMember($project->id, $userId);

        return redirect()->route('projects.members.index', $project)->with('success', 'Member removed successfully.');
    }
}
