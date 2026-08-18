<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Services\FeatureFlagService;
use App\Services\MigrationCompatibilityInspector;
use App\Services\SecurityMonitor;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

test('liveness emits a safe correlation identifier', function (): void {
    $this->getJson('/health/live')
        ->assertOk()
        ->assertJsonPath('status', 'ok')
        ->assertHeader('X-Correlation-ID');

    $this->withHeader('X-Correlation-ID', 'release-smoke-1234')
        ->getJson('/health/live')
        ->assertHeader('X-Correlation-ID', 'release-smoke-1234');
});

test('readiness and aggregate metrics require the operations token', function (): void {
    config()->set('taskflow.operations.token', 'test-operations-token');

    $this->getJson('/health/ready')->assertForbidden();
    $this->withHeader('X-Operations-Token', 'test-operations-token')
        ->getJson('/health/ready')
        ->assertOk()
        ->assertJsonPath('status', 'ready')
        ->assertJsonPath('components.database', true)
        ->assertJsonPath('components.cache', true)
        ->assertJsonPath('components.queue', true)
        ->assertJsonPath('components.storage', true);

    $this->withHeader('X-Operations-Token', 'test-operations-token')
        ->getJson('/health/metrics')
        ->assertOk()
        ->assertJsonStructure(['release', 'metrics' => [
            'request_samples',
            'request_p95_ms',
            'request_error_rate_percent',
            'queued_jobs',
            'oldest_queue_age_seconds',
            'failed_jobs',
            'failed_notifications',
            'failed_webhooks',
            'local_storage_bytes',
        ]]);
});

test('administrator can see operational dashboard while member cannot', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    $member = User::factory()->create();
    $member->assignRole(UserRole::Member->value);

    $this->actingAs($admin)->get('/admin/operations')->assertOk()->assertSee('Operational health');
    $this->actingAs($member)->get('/admin/operations')->assertForbidden();
});

test('feature flags are disabled by default and honor explicit configuration', function (): void {
    $flags = app(FeatureFlagService::class);
    expect($flags->enabled('unconfigured'))->toBeFalse();

    config()->set('taskflow.features.kanban_v2', true);
    expect($flags->enabled('kanban_v2'))->toBeTrue();
});

test('migration compatibility inspector accepts current additive up methods', function (): void {
    $inspector = app(MigrationCompatibilityInspector::class);
    $paths = [database_path('migrations'), ...glob(base_path('Modules/*/database/migrations'), GLOB_ONLYDIR)];

    expect(collect($paths)->flatMap(fn (string $path): array => $inspector->inspect($path))->all())->toBe([]);
});

test('authentication failure monitoring hashes identities', function (): void {
    Log::shouldReceive('channel')->once()->with('structured')->andReturnSelf();
    Log::shouldReceive('warning')->once()->withArgs(function (string $event, array $context): bool {
        return $event === 'taskflow.authentication_failed'
            && $context['channel'] === 'web'
            && $context['identity_hash'] === hash('sha256', 'person@example.com')
            && ! in_array('person@example.com', $context, true);
    });

    app(SecurityMonitor::class)->authenticationFailure('web', 'Person@Example.com');
});
