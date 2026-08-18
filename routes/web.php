<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

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
