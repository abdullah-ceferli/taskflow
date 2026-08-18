<?php

namespace App\Http\Middleware;

use App\Services\CurrentWorkspace;
use App\Services\PerformanceTelemetry;
use App\Services\QueryTelemetry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RecordRequestPerformance
{
    public function __construct(
        private readonly QueryTelemetry $queries,
        private readonly PerformanceTelemetry $performance,
        private readonly CurrentWorkspace $workspace,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('taskflow.performance.telemetry_enabled', true)) {
            return $next($request);
        }

        $this->queries->reset();
        $startedAt = hrtime(true);
        $status = 500;

        try {
            $response = $next($request);
            $status = $response->getStatusCode();

            return $response;
        } finally {
            $actor = $request->user();
            $this->performance->record(
                $request->route()?->getName() ?? $request->method().' '.$request->path(),
                $actor ? $this->workspace->idFor($actor) : null,
                $actor?->id,
                (hrtime(true) - $startedAt) / 1_000_000,
                $status,
                $this->queries->snapshot(),
            );
        }
    }
}
