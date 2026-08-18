<?php

use App\Http\Controllers\Api\V1\AdminUserController;
use App\Http\Controllers\Api\V1\AuthenticationController;
use App\Http\Controllers\Api\V1\GlobalSearchController;
use App\Http\Controllers\Api\V1\ReportExportController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Middleware\ResolveCurrentWorkspace;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/token', [AuthenticationController::class, 'store'])->middleware('throttle:6,1')->name('api.v1.auth.token.store');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('me', [AuthenticationController::class, 'show'])->name('api.v1.me');
        Route::delete('auth/token', [AuthenticationController::class, 'destroy'])->name('api.v1.auth.token.destroy');
        Route::get('search', GlobalSearchController::class)->middleware([ResolveCurrentWorkspace::class, 'ability:projects:read,tasks:read'])->name('api.v1.search');
        Route::middleware([ResolveCurrentWorkspace::class, 'abilities:tasks:read'])->group(function (): void {
            Route::get('reports/exports', [ReportExportController::class, 'index'])->name('api.v1.reports.exports.index');
            Route::post('reports/exports', [ReportExportController::class, 'store'])->name('api.v1.reports.exports.store');
        });
        Route::middleware([ResolveCurrentWorkspace::class, 'abilities:projects:write'])->group(function (): void {
            Route::get('webhooks', [WebhookController::class, 'index'])->name('api.v1.webhooks.index');
            Route::post('webhooks', [WebhookController::class, 'store'])->name('api.v1.webhooks.store');
            Route::post('webhooks/{webhook}/rotate', [WebhookController::class, 'rotate'])->name('api.v1.webhooks.rotate');
            Route::delete('webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('api.v1.webhooks.destroy');
            Route::post('webhook-deliveries/{delivery}/replay', [WebhookController::class, 'replay'])->name('api.v1.webhooks.deliveries.replay');
        });
        Route::middleware([ResolveCurrentWorkspace::class, 'abilities:projects:write'])->group(function (): void {
            Route::get('admin/users', [AdminUserController::class, 'index'])->name('api.v1.admin.users.index');
            Route::patch('admin/users/{user}', [AdminUserController::class, 'update'])->name('api.v1.admin.users.update');
        });
    });
});
