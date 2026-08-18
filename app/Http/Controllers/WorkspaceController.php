<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Services\CurrentWorkspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class WorkspaceController extends Controller
{
    public function index(Request $request, CurrentWorkspace $current): View
    {
        return view('workspaces.index', [
            'workspaces' => $request->user()->workspaces()->orderBy('name')->get(),
            'current' => $current->get(),
        ]);
    }

    public function switch(Request $request, Workspace $workspace, CurrentWorkspace $current): RedirectResponse
    {
        $current->resolve($request->user(), $workspace->id);
        $request->session()->put('current_workspace_id', $workspace->id);

        return redirect()->route('dashboard.index')->with('success', "Switched to {$workspace->name}.");
    }
}
