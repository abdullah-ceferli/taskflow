<?php

namespace Modules\Dashboard\Http\Controllers\Api\V1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\Dashboard\Http\Resources\DashboardSummaryResource;
use Modules\Dashboard\Services\DashboardService;
use Modules\Tasks\Http\Resources\TaskResource;

final class DashboardController
{
    use AuthorizesRequests;

    public function __construct(private readonly DashboardService $dashboard) {}

    public function summary(Request $request): DashboardSummaryResource
    {
        $this->authorize('viewDashboard');

        return new DashboardSummaryResource($this->dashboard->summary($request->user()));
    }

    public function myTasks(Request $request)
    {
        $this->authorize('viewDashboard');

        return TaskResource::collection($this->dashboard->myTasks($request->user()));
    }

    public function overdue(Request $request)
    {
        $this->authorize('viewDashboard');

        return TaskResource::collection($this->dashboard->overdueTasks($request->user()));
    }
}
