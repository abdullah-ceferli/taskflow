<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Exceptions\DomainRuleViolation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Activity\Enums\ActivityEvent;
use Modules\Activity\Services\ActivityRecorder;

final class AdminUserService
{
    public function __construct(private readonly ActivityRecorder $activity) {}

    /** @return LengthAwarePaginator<User> */
    public function paginate(): LengthAwarePaginator
    {
        return User::query()->with('workspaces')->orderBy('name')->paginate(25);
    }

    public function updateRole(User $actor, User $user, UserRole $role): User
    {
        if ($actor->is($user) && $role !== UserRole::Admin) {
            throw new DomainRuleViolation('Administrators cannot remove their own admin role.');
        }

        $old = $user->getRoleNames()->all();
        $user->syncRoles($role->value);
        $this->activity->record(ActivityEvent::UserRoleChanged, $actor, $user, [
            'user_id' => $user->id,
            'old_roles' => $old,
            'new_role' => $role->value,
        ]);

        return $user->load('workspaces');
    }
}
