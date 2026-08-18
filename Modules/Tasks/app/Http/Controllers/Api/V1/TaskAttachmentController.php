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
        $files = $request->file('attachments', []);
        if ($request->hasFile('attachment')) {
            $files[] = $request->file('attachment');
        }
        $attachments = $this->service->uploadMany($task->load('project'), $request->user(), $files);

        if (count($attachments) === 1) {
            return (new TaskAttachmentResource($attachments[0]))->response()->setStatusCode(201);
        }

        return TaskAttachmentResource::collection(collect($attachments))->response()->setStatusCode(201);
    }

    public function download(Task $task, TaskAttachment $attachment)
    {
        abort_unless($attachment->task_id === $task->id, 404);
        $this->authorize('view', $task);

        return $this->service->download($attachment->load('task'), request()->user());
    }

    public function preview(Task $task, TaskAttachment $attachment)
    {
        abort_unless($attachment->task_id === $task->id, 404);
        $this->authorize('view', $task);

        return $this->service->preview($attachment);
    }

    public function destroy(Task $task, TaskAttachment $attachment)
    {
        abort_unless($attachment->task_id === $task->id, 404);
        $this->authorize('delete', $attachment);
        $this->service->delete($attachment->load('task'), request()->user());

        return response()->noContent();
    }
}
