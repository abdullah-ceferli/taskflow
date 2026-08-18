<?php

namespace App\Http\Controllers;

use App\Enums\PermissionName;
use App\Enums\UserRole;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use App\Services\AdminUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminUserController extends Controller
{
    public function index(Request $request, AdminUserService $users): View
    {
        abort_unless($request->user()->hasPermissionTo(PermissionName::UserRolesManage->value), 403);

        return view('admin.users.index', [
            'users' => $users->paginate(),
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(UpdateUserRoleRequest $request, User $user, AdminUserService $users): RedirectResponse
    {
        $role = UserRole::from($request->string('role')->toString());
        $users->updateRole($request->user(), $user, $role);

        return back()->with('success', 'User role updated.');
    }
}
