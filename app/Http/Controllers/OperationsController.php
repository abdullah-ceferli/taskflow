<?php

namespace App\Http\Controllers;

use App\Enums\PermissionName;
use App\Services\OperationalHealthService;
use App\Services\OperationalMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class OperationsController extends Controller
{
    public function live(): JsonResponse
    {
        return response()->json(['status' => 'ok', 'release' => config('taskflow.release')]);
    }

    public function ready(OperationalHealthService $health): JsonResponse
    {
        $result = $health->readiness();

        return response()->json([
            'status' => $result['healthy'] ? 'ready' : 'unavailable',
            'components' => $result['components'],
            'release' => config('taskflow.release'),
        ], $result['healthy'] ? 200 : 503);
    }

    public function metrics(OperationalMetricsService $metrics): JsonResponse
    {
        return response()->json(['release' => config('taskflow.release'), 'metrics' => $metrics->snapshot()]);
    }

    public function index(Request $request, OperationalHealthService $health, OperationalMetricsService $metrics): View
    {
        abort_unless($request->user()->hasPermissionTo(PermissionName::UserRolesManage->value), 403);

        return view('admin.operations.index', [
            'health' => $health->readiness(),
            'metrics' => $metrics->snapshot(),
        ]);
    }
}
