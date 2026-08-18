<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Projects\Models\Project;
use Modules\Tasks\Livewire\QuickTaskCreate;
use Modules\Tasks\Livewire\TaskCommentForm;
use Modules\Tasks\Livewire\TaskFilters;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskComment;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function livewireManager(): User
{
    $user = User::factory()->create();
    $user->assignRole(UserRole::ProjectManager->value);

    return $user;
}

test('task filters renders only the signed-in actor task list', function (): void {
    $manager = livewireManager();
    $project = Project::factory()->create(['owner_id' => $manager->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id, 'title' => 'Visible filter task']);

    $this->actingAs($manager);
    Livewire::test(TaskFilters::class)->assertSee($task->title);
});

test('comment form creates a comment through the service layer', function (): void {
    $manager = livewireManager();
    $project = Project::factory()->create(['owner_id' => $manager->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id]);

    $this->actingAs($manager);
    Livewire::test(TaskCommentForm::class, ['task' => $task])
        ->set('body', 'A useful comment')
        ->call('save')
        ->assertSet('body', '');

    expect(TaskComment::query()->where('task_id', $task->id)->where('body', 'A useful comment')->exists())->toBeTrue();
});

test('quick task creation uses the authorized task service', function (): void {
    $manager = livewireManager();
    $project = Project::factory()->create(['owner_id' => $manager->id]);

    $this->actingAs($manager);
    Livewire::test(QuickTaskCreate::class)
        ->set('projectId', (string) $project->id)
        ->set('title', 'Livewire created task')
        ->set('priority', 'medium')
        ->call('save')
        ->assertSet('title', '');

    expect(Task::query()->where('project_id', $project->id)->where('title', 'Livewire created task')->exists())->toBeTrue();
});
