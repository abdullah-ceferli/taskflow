<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Modules\Projects\Enums\ProjectMemberRole;
use Modules\Projects\Models\Project;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAttachment;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
});

test('attachment API stores and deletes an authorized upload without exposing its path', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    $project = Project::factory()->create(['owner_id' => $admin->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $admin->id]);
    Sanctum::actingAs($admin, ['tasks:write']);

    $response = $this->post("/api/v1/tasks/{$task->id}/attachments", [
        'attachment' => UploadedFile::fake()->create('brief.pdf', 4, 'application/pdf'),
    ], ['Accept' => 'application/json'])->assertCreated()->assertJsonPath('data.original_name', 'brief.pdf');

    $attachment = TaskAttachment::query()->findOrFail($response->json('data.id'));
    Storage::disk('local')->assertExists($attachment->path);
    $response->assertJsonMissingPath('data.path');

    $this->deleteJson("/api/v1/tasks/{$task->id}/attachments/{$attachment->id}")->assertNoContent();
    Storage::disk('local')->assertMissing($attachment->path);
    $this->assertDatabaseMissing('task_attachments', ['id' => $attachment->id]);
});

test('project members API adds a member with the requested role', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    $newMember = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $admin->id]);
    Sanctum::actingAs($admin, ['projects:write']);

    $this->postJson("/api/v1/projects/{$project->id}/members", [
        'user_id' => $newMember->id,
        'member_role' => ProjectMemberRole::Member->value,
    ])->assertCreated()->assertJsonPath('data.user_id', $newMember->id);

    $this->assertDatabaseHas('project_members', ['project_id' => $project->id, 'user_id' => $newMember->id]);
});
