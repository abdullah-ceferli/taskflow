<?php

use App\Enums\UserRole;
use App\Enums\WorkspaceRole;
use App\Exceptions\DomainRuleViolation;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use App\Services\WorkspaceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Sanctum\Sanctum;
use Modules\Projects\Models\Project;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

test('workspace header isolates projects and rejects id tampering', function (): void {
    $manager = User::factory()->create();
    $manager->assignRole(UserRole::ProjectManager->value);
    $outsider = User::factory()->create();
    $outsider->assignRole(UserRole::Member->value);
    $service = app(WorkspaceService::class);
    $alpha = $service->createFor($manager, 'Alpha Workspace');
    $beta = $service->createFor($manager, 'Beta Workspace');
    $alphaProject = Project::factory()->create(['workspace_id' => $alpha->id, 'owner_id' => $manager->id]);
    $betaProject = Project::factory()->create(['workspace_id' => $beta->id, 'owner_id' => $manager->id]);

    Sanctum::actingAs($manager, ['projects:read']);
    $this->withHeader('X-Workspace-Id', (string) $alpha->id)
        ->getJson('/api/v1/projects')
        ->assertOk()
        ->assertJsonPath('data.0.id', $alphaProject->id)
        ->assertJsonMissing(['id' => $betaProject->id]);

    Sanctum::actingAs($outsider, ['projects:read']);
    $this->withHeader('X-Workspace-Id', (string) $alpha->id)
        ->getJson('/api/v1/projects')
        ->assertForbidden();
});

test('workspace invitation is hashed, email-bound, expiring, and single-use', function (): void {
    $owner = User::factory()->create();
    $owner->assignRole(UserRole::ProjectManager->value);
    $invitee = User::factory()->create(['email' => 'invitee@taskflow.test']);
    $other = User::factory()->create(['email' => 'other@taskflow.test']);
    $service = app(WorkspaceService::class);
    $workspace = $service->createFor($owner, 'Secure Workspace');
    $result = $service->invite($workspace, $owner, $invitee->email, WorkspaceRole::Member);

    expect($result['invitation']->token_hash)->not->toBe($result['token'])
        ->and($result['invitation']->expires_at->isFuture())->toBeTrue();

    expect(fn () => $service->accept($other, $result['token']))
        ->toThrow(DomainRuleViolation::class);

    $membership = $service->accept($invitee, $result['token']);
    expect($membership->workspace_id)->toBe($workspace->id)
        ->and(WorkspaceInvitation::query()->findOrFail($result['invitation']->id)->accepted_at)->not->toBeNull();

    expect(fn () => $service->accept($invitee, $result['token']))->toThrow(ModelNotFoundException::class);
});

test('admin user API requires permission and records role changes', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    app(WorkspaceService::class)->createFor($admin, 'Admin Workspace');
    $target = User::factory()->create();
    $target->assignRole(UserRole::Member->value);

    Sanctum::actingAs($admin, ['projects:write']);
    $this->patchJson("/api/v1/admin/users/{$target->id}", ['role' => UserRole::ProjectManager->value])
        ->assertOk()
        ->assertJsonPath('data.roles.0', UserRole::ProjectManager->value);

    expect($target->fresh()->hasRole(UserRole::ProjectManager->value))->toBeTrue();
    $this->assertDatabaseHas('activity_log', ['event' => 'user.role_changed', 'subject_id' => $target->id]);
});
