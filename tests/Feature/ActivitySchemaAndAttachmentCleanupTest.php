<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Modules\Activity\Enums\ActivityEvent;
use Modules\Activity\Services\ActivityRecorder;
use Modules\Projects\Models\Project;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAttachment;
use Spatie\Activitylog\Models\Activity;

beforeEach(function (): void {
    Storage::fake('local');
});

test('activity payloads are versioned and remove sensitive values recursively', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $user->id]);

    app(ActivityRecorder::class)->record(ActivityEvent::TaskUpdated, $user, $task, [
        'project_id' => $project->id,
        'task_id' => $task->id,
        'password' => 'never-log-this',
        'old' => ['title' => 'Before', 'token' => 'never-log-this'],
    ]);

    $properties = Activity::query()->where('subject_type', Task::class)->where('subject_id', $task->id)->latest()->firstOrFail()->properties;

    expect($properties->get('schema_version'))->toBe(1)
        ->and($properties->has('password'))->toBeFalse()
        ->and($properties->get('old'))->toBe(['title' => 'Before']);
});

test('orphan attachment cleanup is dry-run by default and deletes only with force', function (): void {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $user->id]);
    $orphanPath = 'task-attachments/orphan/stale.pdf';
    $knownPath = 'task-attachments/known/retained.pdf';
    Storage::disk('local')->put($orphanPath, 'orphan');
    Storage::disk('local')->put($knownPath, 'referenced');
    TaskAttachment::factory()->create(['task_id' => $task->id, 'uploaded_by' => $user->id, 'disk' => 'local', 'path' => $knownPath]);

    $this->artisan('taskflow:attachments:prune-orphans', ['--retention-days' => 0])
        ->expectsOutputToContain("Would delete: {$orphanPath}")
        ->assertExitCode(0);
    Storage::disk('local')->assertExists($orphanPath);

    $this->artisan('taskflow:attachments:prune-orphans', ['--retention-days' => 0, '--force' => true])
        ->assertExitCode(0);
    Storage::disk('local')->assertMissing($orphanPath);
    Storage::disk('local')->assertExists($knownPath);
});
