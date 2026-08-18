<?php

use App\Enums\PermissionName;
use App\Enums\UserRole;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Tasks\Enums\TaskStatus;

test('core enums expose the canonical values used by policies and services', function (): void {
    expect(UserRole::Admin->value)->toBe('admin')
        ->and(PermissionName::TasksView->value)->toBe('tasks.view')
        ->and(ProjectStatus::Archived->value)->toBe('archived')
        ->and(TaskStatus::Done->value)->toBe('done');
});
