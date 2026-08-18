<?php

namespace App\Http\Controllers;

use App\Enums\PermissionName;
use App\Enums\TokenAbility;
use App\Http\Requests\ManagePersonalAccessTokenRequest;
use App\Services\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

final class PersonalAccessTokenController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasPermissionTo(PermissionName::ApiTokensManage->value), 403);

        return view('tokens.index', ['tokens' => $request->user()->tokens()->latest()->get(), 'abilities' => TokenAbility::cases()]);
    }

    public function store(ManagePersonalAccessTokenRequest $request, AuthenticationService $authentication): RedirectResponse
    {
        abort_unless($request->user()->hasPermissionTo(PermissionName::ApiTokensManage->value), 403);
        $token = $authentication->createForAuthenticatedUser($request->user(), $request->string('current_password')->toString(), $request->string('device_name')->toString(), $request->array('abilities'), $request->integer('expires_in_days'));

        return back()->with('success', 'Token created. Copy it now; it will not be shown again.')->with('new_api_token', $token->plainTextToken);
    }

    public function rotate(Request $request, PersonalAccessToken $token, AuthenticationService $authentication): RedirectResponse
    {
        abort_unless($request->user()->hasPermissionTo(PermissionName::ApiTokensManage->value), 403);
        $replacement = $authentication->rotate($request->user(), $token);

        return back()->with('success', 'Token rotated.')->with('new_api_token', $replacement->plainTextToken);
    }

    public function destroy(Request $request, PersonalAccessToken $token, AuthenticationService $authentication): RedirectResponse
    {
        abort_unless($request->user()->hasPermissionTo(PermissionName::ApiTokensManage->value), 403);
        $authentication->revoke($request->user(), $token);

        return back()->with('success', 'Token revoked.');
    }

    public function destroyAll(Request $request, AuthenticationService $authentication): RedirectResponse
    {
        abort_unless($request->user()->hasPermissionTo(PermissionName::ApiTokensManage->value), 403);
        $authentication->revokeAll($request->user());

        return back()->with('success', 'All personal tokens revoked.');
    }
}
