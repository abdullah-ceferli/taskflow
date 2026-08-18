<?php

use App\Contracts\ExceptionTrackerInterface;
use App\Exceptions\DomainRuleViolation;
use App\Exceptions\IdempotencyConflict;
use App\Exceptions\OptimisticLockConflict;
use App\Http\Middleware\ApiLifecycleHeaders;
use App\Http\Middleware\AttachCorrelationId;
use App\Http\Middleware\RecordRequestPerformance;
use App\Http\Middleware\RequireOperationsToken;
use App\Http\Middleware\ResolveCurrentWorkspace;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([__DIR__.'/../app/Console/Commands'])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [AttachCorrelationId::class, ResolveCurrentWorkspace::class, RecordRequestPerformance::class]);
        $middleware->api(append: [AttachCorrelationId::class, ApiLifecycleHeaders::class, RecordRequestPerformance::class]);
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'operations.token' => RequireOperationsToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $exception): void {
            if ($exception instanceof DomainRuleViolation || $exception instanceof IdempotencyConflict || $exception instanceof OptimisticLockConflict) {
                return;
            }

            app(ExceptionTrackerInterface::class)->capture($exception, app()->runningInConsole() ? null : request());
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (DomainRuleViolation $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()->withInput()->withErrors(['domain' => $exception->getMessage()]);
        });

        $exceptions->render(function (OptimisticLockConflict $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 409);
            }

            return back()->withErrors(['conflict' => $exception->getMessage()]);
        });

        $exceptions->render(function (IdempotencyConflict $exception, Request $request) {
            return response()->json(['message' => $exception->getMessage()], 409);
        });
    })->create();
