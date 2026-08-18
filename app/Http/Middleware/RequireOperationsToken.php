<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireOperationsToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('taskflow.operations.token', '');
        abort_if($expected === '', 404);

        $provided = (string) ($request->header('X-Operations-Token') ?: $request->bearerToken());
        abort_unless($provided !== '' && hash_equals($expected, $provided), 403);

        return $next($request);
    }
}
