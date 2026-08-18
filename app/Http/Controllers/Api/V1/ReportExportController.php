<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreReportExportRequest;
use App\Http\Resources\ReportExportResource;
use App\Services\ReportExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReportExportController
{
    public function index(Request $request, ReportExportService $service)
    {
        return ReportExportResource::collection($service->forActor($request->user()));
    }

    public function store(StoreReportExportRequest $request, ReportExportService $service): JsonResponse
    {
        return (new ReportExportResource($service->create($request->user(), $request->string('type')->toString() ?: 'tasks')))->response()->setStatusCode(202);
    }
}
