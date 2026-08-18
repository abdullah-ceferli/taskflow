<?php

use App\Http\Controllers\ActivityExportController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\PersonalAccessTokenController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WorkspaceCapacityController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceInvitationController;
use Illuminate\Support\Facades\Route;

Route::get('/health/live', [OperationsController::class, 'live'])->name('health.live');
Route::middleware('operations.token')->group(function (): void {
    Route::get('/health/ready', [OperationsController::class, 'ready'])->name('health.ready');
    Route::get('/health/metrics', [OperationsController::class, 'metrics'])->name('health.metrics');
});

Route::get('/', fn () => redirect()->route('dashboard.index'))
    ->middleware('auth')
    ->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthenticatedSessionController::class, 'register'])->name('register');
    Route::post('/register', [AuthenticatedSessionController::class, 'registerStore'])->name('register.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/workspaces', [WorkspaceController::class, 'index'])->name('workspaces.index');
    Route::post('/workspaces/{workspace}/switch', [WorkspaceController::class, 'switch'])->name('workspaces.switch');
    Route::get('/workspace/invitations', [WorkspaceInvitationController::class, 'index'])->name('workspace.invitations.index');
    Route::post('/workspace/invitations', [WorkspaceInvitationController::class, 'store'])->name('workspace.invitations.store');
    Route::get('/workspace/invitations/{token}/accept', [WorkspaceInvitationController::class, 'accept'])->name('workspace.invitations.accept');
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/operations', [OperationsController::class, 'index'])->name('admin.operations.index');
    Route::patch('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/admin/activity/export', ActivityExportController::class)->name('admin.activity.export');
    Route::get('/notification-preferences', [NotificationPreferenceController::class, 'index'])->name('notifications.preferences.index');
    Route::put('/notification-preferences', [NotificationPreferenceController::class, 'update'])->name('notifications.preferences.update');
    Route::patch('/workspace-members/{workspaceMember}/capacity', [WorkspaceCapacityController::class, 'update'])->name('workspace-members.capacity.update');
    Route::get('/search', GlobalSearchController::class)->name('search');
    Route::get('/api-tokens', [PersonalAccessTokenController::class, 'index'])->name('tokens.index');
    Route::post('/api-tokens', [PersonalAccessTokenController::class, 'store'])->name('tokens.store');
    Route::post('/api-tokens/{token}/rotate', [PersonalAccessTokenController::class, 'rotate'])->name('tokens.rotate');
    Route::delete('/api-tokens/{token}', [PersonalAccessTokenController::class, 'destroy'])->name('tokens.destroy');
    Route::delete('/api-tokens', [PersonalAccessTokenController::class, 'destroyAll'])->name('tokens.destroy-all');
    Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::post('/webhooks/{webhook}/rotate', [WebhookController::class, 'rotate'])->name('webhooks.rotate');
    Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::post('/webhook-deliveries/{delivery}/replay', [WebhookController::class, 'replay'])->name('webhooks.deliveries.replay');
    Route::get('/reports/exports', [ReportExportController::class, 'index'])->name('reports.exports.index');
    Route::post('/reports/exports', [ReportExportController::class, 'store'])->name('reports.exports.store');
    Route::get('/reports/exports/{export}/download', [ReportExportController::class, 'download'])->middleware('signed')->name('reports.exports.download');
});
