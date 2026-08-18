<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateWorkspaceCapacityRequest;
use App\Models\WorkspaceMember;
use App\Services\CurrentWorkspace;
use Illuminate\Http\RedirectResponse;

final class WorkspaceCapacityController extends Controller
{
    public function update(UpdateWorkspaceCapacityRequest $request, WorkspaceMember $workspaceMember, CurrentWorkspace $current): RedirectResponse
    {
        abort_unless($current->canManage($request->user()) && $workspaceMember->workspace_id === $current->idFor($request->user()), 403);
        $workspaceMember->update(['weekly_capacity_hours' => $request->float('weekly_capacity_hours')]);

        return back()->with('success', 'Capacity updated.');
    }
}
