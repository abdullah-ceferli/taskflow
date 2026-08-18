<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\Api\V1\DashboardController;

Route::middleware('abilities:dashboard:read')->group(function (): void {
    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');
    Route::get('/dashboard/my-tasks', [DashboardController::class, 'myTasks'])->name('dashboard.my-tasks');
    Route::get('/dashboard/overdue', [DashboardController::class, 'overdue'])->name('dashboard.overdue');
});
