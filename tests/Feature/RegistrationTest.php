<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

test('guests can register and receive the member role', function (): void {
    $response = $this->post('/register', [
        'name' => 'New Member',
        'email' => 'new.member@example.test',
        'password' => 'secure-password',
        'password_confirmation' => 'secure-password',
    ]);

    $response->assertRedirect(route('dashboard.index'));
    $this->assertAuthenticated();
    $user = User::query()->where('email', 'new.member@example.test')->firstOrFail();
    expect($user->hasRole(UserRole::Member->value))->toBeTrue()
        ->and($user->workspaces()->count())->toBe(1);
});

test('registration rejects duplicate email addresses', function (): void {
    User::factory()->create(['email' => 'taken@example.test']);

    $this->from('/register')->post('/register', [
        'name' => 'Another Member',
        'email' => 'taken@example.test',
        'password' => 'secure-password',
        'password_confirmation' => 'secure-password',
    ])->assertRedirect('/register')->assertSessionHasErrors('email');
});
