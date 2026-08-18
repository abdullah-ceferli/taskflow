<?php

namespace Modules\Tasks\Http\Controllers\Api\V1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Tasks\Http\Requests\Api\V1\StoreTaskCommentRequest;
use Modules\Tasks\Http\Resources\TaskCommentResource;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskComment;
use Modules\Tasks\Repositories\Contracts\TaskCommentRepositoryInterface;
use Modules\Tasks\Services\TaskCommentService;

final class TaskCommentController
{
    use AuthorizesRequests;

    public function __construct(private readonly TaskCommentRepositoryInterface $comments, private readonly TaskCommentService $service) {}

    public function index(Task $task)
    {
        $this->authorize('view', $task);

        return TaskCommentResource::collection($this->comments->forTask($task));
    }

    public function store(StoreTaskCommentRequest $request, Task $task): JsonResponse
    {
        $this->authorize('create', [TaskComment::class, $task]);
        $comment = $this->service->create($task->load('project'), $request->user(), $request->string('body')->toString());

        return (new TaskCommentResource($comment))->response()->setStatusCode(201);
    }

    public function destroy(Task $task, TaskComment $comment)
    {
        abort_unless($comment->task_id === $task->id, 404);
        $this->authorize('delete', $comment);
        $this->service->delete($comment->load('task'), request()->user());

        return response()->noContent();
    }
}
