<?php

namespace Modules\Tasks\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Modules\Tasks\Http\Requests\Api\V1\StoreSavedTaskViewRequest;
use Modules\Tasks\Http\Resources\SavedTaskViewResource;
use Modules\Tasks\Models\SavedTaskView;
use Modules\Tasks\Services\SavedTaskViewService;

final class SavedTaskViewController
{
    public function index(SavedTaskViewService $views)
    {
        return SavedTaskViewResource::collection($views->list(request()->user()));
    }

    public function store(StoreSavedTaskViewRequest $request, SavedTaskViewService $views): JsonResponse
    {
        $view = $views->create($request->user(), $request->string('name')->toString(), $request->validated('filters'));

        return (new SavedTaskViewResource($view))->response()->setStatusCode(201);
    }

    public function destroy(SavedTaskView $savedTaskView, SavedTaskViewService $views)
    {
        $views->delete(request()->user(), $savedTaskView);

        return response()->noContent();
    }
}
