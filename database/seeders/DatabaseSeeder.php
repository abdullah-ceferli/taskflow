<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\WorkspaceService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $admin = User::firstOrCreate(
            ['email' => 'admin@taskflow.test'],
            ['name' => 'Admin User', 'password' => bcrypt('1234')],
        );
        $admin->forceFill(['name' => 'Admin User', 'password' => bcrypt('1234')])->save();
        $admin->syncRoles(UserRole::Admin->value);

        $manager = User::firstOrCreate(
            ['email' => 'manager@taskflow.test'],
            ['name' => 'Manager User', 'password' => bcrypt('password')],
        );
        $manager->syncRoles(UserRole::ProjectManager->value);

        $member = User::firstOrCreate(
            ['email' => 'member@taskflow.test'],
            ['name' => 'Member User', 'password' => bcrypt('password')],
        );
        $member->syncRoles(UserRole::Member->value);

        $workspaces = app(WorkspaceService::class);
        $workspace = $admin->workspaces()->first() ?? $workspaces->createFor($admin, 'TaskFlow Workspace');

        foreach ([[$manager, 'manager'], [$member, 'member']] as [$user, $role]) {
            $workspace->members()->syncWithoutDetaching([$user->id => ['role' => $role, 'joined_at' => now()]]);
        }
    }
}
