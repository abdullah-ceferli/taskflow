<?php

use App\Contracts\GlobalSearchInterface;
use App\Enums\TokenAbility;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\AuthenticationService;
use App\Services\CurrentWorkspace;
use App\Services\ReportExportService;
use App\Services\WebhookService;
use App\Services\WorkspaceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Modules\Projects\Models\Project;
use Modules\Tasks\Enums\TaskPriority;
use Modules\Tasks\Events\TaskCreated;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskComment;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
});

function integrationContext(): array
{
    $manager = User::factory()->create();
    $manager->assignRole(UserRole::ProjectManager->value);
    $workspace = app(WorkspaceService::class)->createFor($manager, 'Integration Workspace');
    app(CurrentWorkspace::class)->resolve($manager, $workspace->id);
    $project = Project::factory()->create(['workspace_id' => $workspace->id, 'owner_id' => $manager->id]);

    return [$manager, $workspace, $project];
}

test('global search never returns records from another workspace', function (): void {
    [$manager, $workspace, $project] = integrationContext();
    $visible = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id, 'title' => 'Needle visible task']);
    TaskComment::factory()->create(['task_id' => $visible->id, 'user_id' => $manager->id, 'body' => 'Needle visible comment']);

    $otherWorkspace = app(WorkspaceService::class)->createFor($manager, 'Other Integration Workspace');
    $otherProject = Project::factory()->create(['workspace_id' => $otherWorkspace->id, 'owner_id' => $manager->id, 'name' => 'Needle hidden project']);
    $hidden = Task::factory()->create(['project_id' => $otherProject->id, 'creator_id' => $manager->id, 'title' => 'Needle hidden task']);
    TaskComment::factory()->create(['task_id' => $hidden->id, 'user_id' => $manager->id, 'body' => 'Needle hidden comment']);

    app(CurrentWorkspace::class)->resolve($manager, $workspace->id);
    $results = app(GlobalSearchInterface::class)->search($manager, 'Needle');
    expect($results->pluck('title')->join(' '))->toContain('visible')->not->toContain('hidden');

    Sanctum::actingAs($manager, [TokenAbility::ProjectsRead->value, TokenAbility::TasksRead->value]);
    $this->withHeader('X-Workspace-Id', (string) $workspace->id)->getJson('/api/v1/search?q=Needle')
        ->assertOk()->assertHeader('X-API-Version', 'v1')->assertHeader('Deprecation', 'false')
        ->assertJsonFragment(['id' => $visible->id, 'type' => 'task'])
        ->assertDontSee('Needle hidden task');
});

test('personal tokens use selectable scopes expiry rotation and revocation', function (): void {
    [$manager] = integrationContext();
    $created = app(AuthenticationService::class)->createForAuthenticatedUser($manager, 'password', 'Automation device', [TokenAbility::TasksRead->value], 7);

    expect($created->accessToken->abilities)->toBe([TokenAbility::TasksRead->value])
        ->and($created->accessToken->expires_at->isBetween(now()->addDays(6), now()->addDays(8)))->toBeTrue();

    $oldId = $created->accessToken->id;
    $rotated = app(AuthenticationService::class)->rotate($manager, $created->accessToken);
    expect($rotated->plainTextToken)->toBeString()->not->toBeEmpty()
        ->and($rotated->accessToken->abilities)->toBe([TokenAbility::TasksRead->value]);
    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $oldId]);
    app(AuthenticationService::class)->revokeAll($manager);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('automated task creation replays one response and rejects changed payloads', function (): void {
    [$manager, $workspace, $project] = integrationContext();
    Sanctum::actingAs($manager, [TokenAbility::TasksWrite->value, TokenAbility::TasksRead->value]);
    $payload = ['project_id' => $project->id, 'title' => 'Idempotent automated task', 'priority' => TaskPriority::High->value];
    $headers = ['X-Workspace-Id' => (string) $workspace->id, 'Idempotency-Key' => 'automation-run-42'];

    $first = $this->withHeaders($headers)->postJson('/api/v1/tasks', $payload)->assertCreated()->assertHeader('Idempotency-Replayed', 'false');
    $second = $this->withHeaders($headers)->postJson('/api/v1/tasks', $payload)->assertCreated()->assertHeader('Idempotency-Replayed', 'true');
    expect($second->json('data.id'))->toBe($first->json('data.id'));
    $this->assertDatabaseCount('tasks', 1);

    $this->withHeaders($headers)->postJson('/api/v1/tasks', [...$payload, 'title' => 'Changed request'])->assertConflict();
});

test('webhook signatures verify and replay creates a separate delivery log', function (): void {
    [$manager, , $project] = integrationContext();
    $created = app(WebhookService::class)->create($manager, 'Task automation', 'https://hooks.taskflow.test/events', ['task.created']);
    $captured = [];
    Http::fake(function (ClientRequest $request) use (&$captured) {
        $captured[] = $request;

        return Http::response('', 204);
    });
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id]);
    TaskCreated::dispatch($manager, $task, ['project_id' => $project->id, 'task_id' => $task->id], 'webhook-event-1');

    $request = $captured[0];
    $timestamp = $request->header('X-TaskFlow-Timestamp')[0];
    $expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$request->body(), $created['secret']);
    expect($request->header('X-TaskFlow-Signature')[0])->toBe($expected);
    $this->assertDatabaseHas('webhook_deliveries', ['event' => 'task.created', 'status' => 'delivered', 'attempts' => 1]);

    $delivery = $created['subscription']->deliveries()->firstOrFail();
    app(WebhookService::class)->replay($delivery, $manager);
    $this->assertDatabaseCount('webhook_deliveries', 2);
    expect($captured)->toHaveCount(2);
});

test('queued task exports contain only actor visible workspace rows and expire', function (): void {
    [$manager, $workspace, $project] = integrationContext();
    $visible = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id, 'title' => 'Visible export row']);
    $otherManager = User::factory()->create();
    $otherManager->assignRole(UserRole::ProjectManager->value);
    $otherWorkspace = app(WorkspaceService::class)->createFor($otherManager, 'Export Foreign');
    $otherProject = Project::factory()->create(['workspace_id' => $otherWorkspace->id, 'owner_id' => $otherManager->id]);
    Task::factory()->create(['project_id' => $otherProject->id, 'creator_id' => $otherManager->id, 'title' => 'Hidden export row']);

    app(CurrentWorkspace::class)->resolve($manager, $workspace->id);
    $export = app(ReportExportService::class)->create($manager)->fresh();
    expect($export->status)->toBe('ready')->and($export->path)->not->toBeNull();
    $csv = Storage::disk('local')->get($export->path);
    expect($csv)->toContain($visible->number)->not->toContain('Hidden export row');

    $url = app(ReportExportService::class)->downloadUrl($export);
    $this->actingAs($manager)->withSession(['current_workspace_id' => $workspace->id])->get($url)->assertOk();
    $export->update(['expires_at' => now()->subMinute()]);
    expect(app(ReportExportService::class)->downloadUrl($export->fresh()))->toBeNull();
});
