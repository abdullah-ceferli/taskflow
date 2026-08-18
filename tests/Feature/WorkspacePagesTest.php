<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

test('an administrator can render every main workspace page', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);

    $this->actingAs($admin)->get('/dashboard')->assertOk();
    $this->actingAs($admin)->get('/projects')->assertOk();
    $this->actingAs($admin)->get('/tasks')->assertOk();
    $this->actingAs($admin)->get('/activity')->assertOk();
});
