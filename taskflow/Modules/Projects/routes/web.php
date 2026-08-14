<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\Http\Controllers\Web\ProjectController;
use Modules\Projects\Http\Controllers\Web\ProjectMemberController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Projects routes
    Route::resource('projects', ProjectController::class)->names('projects');
    Route::post('projects/{project}/archive', [ProjectController::class, 'archive'])->name('projects.archive');

    // Project members routes
    Route::resource('projects.members', ProjectMemberController::class)->names('projects.members');
});
