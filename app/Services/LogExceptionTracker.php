<?php

namespace App\Services;

use App\Contracts\ExceptionTrackerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class LogExceptionTracker implements ExceptionTrackerInterface
{
    public function capture(Throwable $exception, ?Request $request = null): void
    {
        Log::channel('structured')->error('taskflow.exception', [
            'exception_class' => $exception::class,
            'correlation_id' => $request?->attributes->get('correlation_id'),
            'route' => $request?->route()?->getName(),
            'method' => $request?->method(),
            'path' => $request?->path(),
        ]);
    }
}
