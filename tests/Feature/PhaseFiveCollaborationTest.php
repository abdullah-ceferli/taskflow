<?php

use App\Enums\UserRole;
use App\Exceptions\DomainRuleViolation;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\MentionedInCommentNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskDueSoonNotification;
use App\Services\CurrentWorkspace;
use App\Services\WorkspaceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Activity\Enums\ActivityEvent;
use Modules\Activity\Services\ActivityRecorder;
use Modules\Projects\Enums\ProjectMemberRole;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectMember;
use Modules\Tasks\Data\TaskFiltersData;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Livewire\TaskFilters;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskLabel;
use Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;
use Modules\Tasks\Services\SavedTaskViewService;
use Modules\Tasks\Services\TaskAssignmentService;
use Modules\Tasks\Services\TaskAttachmentService;
use Modules\Tasks\Services\TaskCommentService;
use Modules\Tasks\Services\TaskLabelService;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
});

function collaborationWorkspace(): array
{
    $manager = User::factory()->create();
    $manager->assignRole(UserRole::ProjectManager->value);
    $workspace = app(WorkspaceService::class)->createFor($manager, 'Collaboration Workspace');
    app(CurrentWorkspace::class)->resolve($manager, $workspace->id);
    $project = Project::factory()->create(['workspace_id' => $workspace->id, 'owner_id' => $manager->id]);

    return [$manager, $workspace, $project];
}

test('label filters and saved views stay inside the current workspace', function (): void {
    [$manager, $workspace, $project] = collaborationWorkspace();
    $labels = app(TaskLabelService::class);
    $release = $labels->create($manager, 'Release', '#4f46e5', $project->id);
    $visible = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id, 'title' => 'Release task']);
    $hidden = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id, 'title' => 'Other task']);
    $labels->sync($visible, $manager, [$release->id]);

    $ids = app(TaskRepositoryInterface::class)->paginateFor($manager, TaskFiltersData::fromArray(['label_ids' => [$release->id]]))->pluck('id');
    expect($ids)->toContain($visible->id)->not->toContain($hidden->id);

    Livewire::actingAs($manager)->test(TaskFilters::class)
        ->set('labelIds', [$release->id])
        ->assertSee('Release task')
        ->assertDontSee('Other task');

    $view = app(SavedTaskViewService::class)->create($manager, 'Release work', ['label_ids' => [$release->id], 'status' => TaskStatus::Todo->value, 'unsafe' => 'discard']);
    expect($view->filters)->toHaveKeys(['label_ids', 'status'])->not->toHaveKey('unsafe');

    $otherOwner = User::factory()->create();
    $otherOwner->assignRole(UserRole::ProjectManager->value);
    $otherWorkspace = app(WorkspaceService::class)->createFor($otherOwner, 'Other Workspace');
    $foreignLabel = TaskLabel::query()->create(['workspace_id' => $otherWorkspace->id, 'name' => 'Foreign', 'color' => '#000000']);
    expect(fn () => $labels->sync($visible, $manager, [$foreignLabel->id]))->toThrow(DomainRuleViolation::class);
});

test('assignment and mention notifications respect workspace preferences', function (): void {
    [$manager, $workspace, $project] = collaborationWorkspace();
    $member = User::factory()->create(['email' => 'mentioned@taskflow.test']);
    $member->assignRole(UserRole::Member->value);
    ProjectMember::query()->create(['project_id' => $project->id, 'user_id' => $member->id, 'member_role' => ProjectMemberRole::Member, 'joined_at' => now()]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id]);

    Notification::fake();
    app(TaskAssignmentService::class)->assign($task, $member, $manager);
    Notification::assertSentTo($member, TaskAssignedNotification::class);

    app(TaskCommentService::class)->create($task, $manager, 'Please review @mentioned@taskflow.test');
    Notification::assertSentTo($member, MentionedInCommentNotification::class);

    NotificationPreference::query()->create(['workspace_id' => $workspace->id, 'user_id' => $member->id, 'event' => 'comment.mentioned', 'in_app' => false, 'email' => false]);
    Notification::fake();
    app(TaskCommentService::class)->create($task, $manager, 'Muted @mentioned@taskflow.test');
    Notification::assertNothingSentTo($member);
});

test('attachment metadata quota and download audit are enforced', function (): void {
    [$manager, $workspace, $project] = collaborationWorkspace();
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id]);
    $service = app(TaskAttachmentService::class);
    $attachment = $service->upload($task->load('project'), $manager, UploadedFile::fake()->createWithContent('brief.txt', 'private content'));

    expect($attachment->metadata_version)->toBe(1)
        ->and($attachment->checksum)->toHaveLength(64)
        ->and($attachment->scan_status)->toBe('not_scanned');

    $service->download($attachment->load('task'), $manager);
    expect($attachment->fresh()->download_count)->toBe(1);
    $this->assertDatabaseHas('activity_log', ['event' => 'attachment.downloaded', 'subject_id' => $attachment->id]);

    config()->set('taskflow.attachments.workspace_quota_bytes', 1);
    expect(fn () => $service->upload($task->load('project'), $manager, UploadedFile::fake()->createWithContent('too-large.txt', 'xx')))
        ->toThrow(DomainRuleViolation::class, 'quota');
});

test('due soon notification delivery is idempotent for each task and day', function (): void {
    [$manager, $workspace, $project] = collaborationWorkspace();
    $member = User::factory()->create();
    $member->assignRole(UserRole::Member->value);
    ProjectMember::query()->create(['project_id' => $project->id, 'user_id' => $member->id, 'member_role' => ProjectMemberRole::Member, 'joined_at' => now()]);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $manager->id,
        'assignee_id' => $member->id,
        'due_at' => today()->addDay(),
    ]);

    NotificationPreference::query()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $member->id,
        'event' => 'task.due_soon',
        'in_app' => false,
        'email' => true,
    ]);
    Notification::fake();
    $this->artisan('taskflow:tasks:notify-due-soon')->assertSuccessful();
    $this->artisan('taskflow:tasks:notify-due-soon')->assertSuccessful();

    Notification::assertSentToTimes($member, TaskDueSoonNotification::class, 1);
    $this->assertDatabaseCount('notification_deliveries', 1);
    $this->assertDatabaseHas('notification_deliveries', [
        'workspace_id' => $workspace->id,
        'user_id' => $member->id,
        'event' => 'task.due_soon',
        'subject_id' => $task->id,
    ]);
});

test('administrator audit export is restricted to the selected workspace', function (): void {
    [$admin, $alpha, $alphaProject] = collaborationWorkspace();
    $admin->syncRoles([UserRole::Admin->value]);
    $beta = app(WorkspaceService::class)->createFor($admin, 'Beta Workspace');
    $betaProject = Project::factory()->create(['workspace_id' => $beta->id, 'owner_id' => $admin->id]);
    $recorder = app(ActivityRecorder::class);

    app(CurrentWorkspace::class)->resolve($admin, $alpha->id);
    $recorder->record(ActivityEvent::ProjectUpdated, $admin, $alphaProject, ['project_id' => $alphaProject->id, 'marker' => 'alpha-export-marker']);
    app(CurrentWorkspace::class)->resolve($admin, $beta->id);
    $recorder->record(ActivityEvent::ProjectUpdated, $admin, $betaProject, ['project_id' => $betaProject->id, 'marker' => 'beta-export-marker']);

    $response = $this->actingAs($admin)
        ->withSession(['current_workspace_id' => $alpha->id])
        ->get(route('admin.activity.export'));

    $response->assertOk();
    expect($response->streamedContent())
        ->toContain('alpha-export-marker')
        ->not->toContain('beta-export-marker');
});
