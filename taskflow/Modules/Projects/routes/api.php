<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\Http\Controllers\Api\V1\ProjectController;
use Modules\Projects\Http\Controllers\Api\V1\ProjectMemberController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Projects API routes
    Route::apiResource('projects', ProjectController::class)->names('api.projects');
    Route::post('projects/{project}/archive', [ProjectController::class, 'archive'])->name('api.projects.archive');

    // Project members API routes
    Route::apiResource('projects.members', ProjectMemberController::class)
        ->only(['index', 'store', 'destroy'])
        ->names('api.projects.members');
});
