<?php

use Illuminate\Support\Facades\Route;
use Modules\Tasks\Http\Controllers\Api\V1\RecurringTaskController;
use Modules\Tasks\Http\Controllers\Api\V1\SavedTaskViewController;
use Modules\Tasks\Http\Controllers\Api\V1\TaskAttachmentController;
use Modules\Tasks\Http\Controllers\Api\V1\TaskBoardController;
use Modules\Tasks\Http\Controllers\Api\V1\TaskCommentController;
use Modules\Tasks\Http\Controllers\Api\V1\TaskController;
use Modules\Tasks\Http\Controllers\Api\V1\TaskDependencyController;
use Modules\Tasks\Http\Controllers\Api\V1\TaskLabelController;

Route::middleware('abilities:tasks:read')->group(function (): void {
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{task}/comments', [TaskCommentController::class, 'index'])->name('tasks.comments.index');
    Route::get('/tasks/{task}/attachments', [TaskAttachmentController::class, 'index'])->name('tasks.attachments.index');
    Route::get('/tasks/{task}/attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])->name('tasks.attachments.download');
    Route::get('/tasks/{task}/attachments/{attachment}/preview', [TaskAttachmentController::class, 'preview'])->name('tasks.attachments.preview');
    Route::get('/labels', [TaskLabelController::class, 'index'])->name('labels.index');
    Route::get('/task-views', [SavedTaskViewController::class, 'index'])->name('task-views.index');
    Route::get('/projects/{project}/board', TaskBoardController::class)->name('projects.board');
    Route::get('/projects/{project}/recurring-tasks', [RecurringTaskController::class, 'index'])->name('projects.recurring-tasks.index');
});
Route::middleware('abilities:tasks:write')->group(function (): void {
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('/tasks/{task}/assignee', [TaskController::class, 'assign'])->name('tasks.assign');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'changeStatus'])->name('tasks.status');
    Route::post('/tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])->name('tasks.attachments.store');
    Route::delete('/tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])->name('tasks.attachments.destroy');
    Route::post('/labels', [TaskLabelController::class, 'store'])->name('labels.store');
    Route::put('/tasks/{task}/labels', [TaskLabelController::class, 'sync'])->name('tasks.labels.sync');
    Route::post('/task-views', [SavedTaskViewController::class, 'store'])->name('task-views.store');
    Route::delete('/task-views/{savedTaskView}', [SavedTaskViewController::class, 'destroy'])->name('task-views.destroy');
    Route::post('/tasks/{task}/dependencies', [TaskDependencyController::class, 'store'])->name('tasks.dependencies.store');
    Route::delete('/tasks/{task}/dependencies/{dependency}', [TaskDependencyController::class, 'destroy'])->name('tasks.dependencies.destroy');
    Route::post('/projects/{project}/recurring-tasks', [RecurringTaskController::class, 'store'])->name('projects.recurring-tasks.store');
    Route::delete('/projects/{project}/recurring-tasks/{recurringTask}', [RecurringTaskController::class, 'destroy'])->name('projects.recurring-tasks.destroy');
});
Route::middleware('abilities:comments:write')->group(function (): void {
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
    Route::delete('/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('tasks.comments.destroy');
});
