<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\Http\Controllers\Api\V1\ProjectController;
use Modules\Projects\Http\Controllers\Api\V1\ProjectMilestoneController;

Route::middleware('abilities:projects:read')->group(function (): void {
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/members', [ProjectController::class, 'members'])->name('projects.members.index');
    Route::get('/projects/{project}/milestones', [ProjectMilestoneController::class, 'index'])->name('projects.milestones.index');
});

Route::middleware('abilities:projects:write')->group(function (): void {
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('/projects/{project}/members', [ProjectController::class, 'storeMember'])->name('projects.members.store');
    Route::delete('/projects/{project}/members/{member}', [ProjectController::class, 'destroyMember'])->name('projects.members.destroy');
    Route::post('/projects/{project}/milestones', [ProjectMilestoneController::class, 'store'])->name('projects.milestones.store');
    Route::patch('/projects/{project}/milestones/{milestone}/complete', [ProjectMilestoneController::class, 'complete'])->name('projects.milestones.complete');
});
