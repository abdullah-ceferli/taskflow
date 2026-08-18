<?php

namespace App\Http\Middleware;

use App\Services\CurrentWorkspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResolveCurrentWorkspace
{
    public function __construct(private readonly CurrentWorkspace $current) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $requestedId = $request->header('X-Workspace-Id')
                ?: ($request->hasSession() ? $request->session()->get('current_workspace_id') : null);
            $workspace = $this->current->resolve($request->user(), $requestedId ? (int) $requestedId : null);

            if ($workspace && $request->hasSession()) {
                $request->session()->put('current_workspace_id', $workspace->id);
            }
        }

        return $next($request);
    }
}
