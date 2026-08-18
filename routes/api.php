<?php

use App\Http\Controllers\Api\V1\AuthenticationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/token', [AuthenticationController::class, 'store'])->middleware('throttle:6,1')->name('api.v1.auth.token.store');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('me', [AuthenticationController::class, 'show'])->name('api.v1.me');
        Route::delete('auth/token', [AuthenticationController::class, 'destroy'])->name('api.v1.auth.token.destroy');
    });
});
