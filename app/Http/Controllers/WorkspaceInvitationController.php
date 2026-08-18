<?php

namespace App\Http\Controllers;

use App\Enums\WorkspaceRole;
use App\Http\Requests\InviteWorkspaceMemberRequest;
use App\Services\CurrentWorkspace;
use App\Services\WorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class WorkspaceInvitationController extends Controller
{
    public function index(Request $request, CurrentWorkspace $current, WorkspaceService $workspaces): View
    {
        $workspace = $current->get();
        abort_unless($workspace, 404);
        $workspaces->ensureManager($workspace, $request->user());

        return view('workspaces.invitations', [
            'workspace' => $workspace,
            'memberships' => $workspace->memberships()->with('user')->get(),
            'invitations' => $workspace->invitations()->latest()->get(),
            'roles' => [WorkspaceRole::Manager, WorkspaceRole::Member],
        ]);
    }

    public function store(InviteWorkspaceMemberRequest $request, CurrentWorkspace $current, WorkspaceService $workspaces): RedirectResponse
    {
        $result = $workspaces->invite(
            $current->get() ?? abort(404),
            $request->user(),
            $request->string('email')->toString(),
            WorkspaceRole::from($request->string('role')->toString()),
        );

        return back()->with('success', 'Invitation created. Share this one-time token securely: '.$result['token']);
    }

    public function accept(Request $request, string $token, WorkspaceService $workspaces): RedirectResponse
    {
        $membership = $workspaces->accept($request->user(), $token);
        $request->session()->put('current_workspace_id', $membership->workspace_id);

        return redirect()->route('dashboard.index')->with('success', 'Workspace invitation accepted.');
    }
}
