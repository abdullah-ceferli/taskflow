<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PermissionName;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Resources\AdminUserResource;
use App\Models\User;
use App\Services\AdminUserService;
use Illuminate\Http\Request;

final class AdminUserController extends Controller
{
    public function index(Request $request, AdminUserService $users)
    {
        abort_unless($request->user()->hasPermissionTo(PermissionName::UserRolesManage->value), 403);

        return AdminUserResource::collection($users->paginate());
    }

    public function update(UpdateUserRoleRequest $request, User $user, AdminUserService $users): AdminUserResource
    {
        $role = UserRole::from($request->string('role')->toString());

        return new AdminUserResource($users->updateRole($request->user(), $user, $role));
    }
}
