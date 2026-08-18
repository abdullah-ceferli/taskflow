<?php

namespace Modules\Tasks\Http\Controllers\Api\V1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Tasks\Http\Requests\Api\V1\StoreTaskAttachmentRequest;
use Modules\Tasks\Http\Resources\TaskAttachmentResource;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAttachment;
use Modules\Tasks\Repositories\Contracts\TaskAttachmentRepositoryInterface;
use Modules\Tasks\Services\TaskAttachmentService;

final class TaskAttachmentController
{
    use AuthorizesRequests;

    public function __construct(private readonly TaskAttachmentRepositoryInterface $attachments, private readonly TaskAttachmentService $service) {}

    public function index(Task $task)
    {
        $this->authorize('view', $task);

        return TaskAttachmentResource::collection($this->attachments->forTask($task));
    }

    public function store(StoreTaskAttachmentRequest $request, Task $task): JsonResponse
    {
        $this->authorize('create', [TaskAttachment::class, $task]);
        $attachment = $this->service->upload($task->load('project'), $request->user(), $request->file('attachment'));

        return (new TaskAttachmentResource($attachment))->response()->setStatusCode(201);
    }

    public function download(Task $task, TaskAttachment $attachment)
    {
        abort_unless($attachment->task_id === $task->id, 404);
        $this->authorize('view', $task);

        return $this->service->download($attachment);
    }

    public function destroy(Task $task, TaskAttachment $attachment)
    {
        abort_unless($attachment->task_id === $task->id, 404);
        $this->authorize('delete', $attachment);
        $this->service->delete($attachment->load('task'), request()->user());

        return response()->noContent();
    }
}
