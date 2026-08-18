<?php

namespace Modules\Tasks\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Modules\Tasks\Http\Requests\UploadTaskAttachmentRequest;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAttachment;
use Modules\Tasks\Services\TaskAttachmentService;

class TaskAttachmentController
{
    use AuthorizesRequests;

    public function __construct(private readonly TaskAttachmentService $attachments) {}

    public function store(UploadTaskAttachmentRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('create', [TaskAttachment::class, $task]);
        $files = $request->file('attachments', []);
        if ($request->hasFile('attachment')) {
            $files[] = $request->file('attachment');
        }
        $this->attachments->uploadMany($task->load('project'), $request->user(), $files);

        return back()->with('success', 'Attachment uploaded.');
    }

    public function download(Task $task, TaskAttachment $attachment)
    {
        abort_unless($attachment->task_id === $task->id, 404);
        $this->authorize('view', $task);

        return $this->attachments->download($attachment->load('task'), request()->user());
    }

    public function preview(Task $task, TaskAttachment $attachment)
    {
        abort_unless($attachment->task_id === $task->id, 404);
        $this->authorize('view', $task);

        return $this->attachments->preview($attachment);
    }

    public function destroy(Task $task, TaskAttachment $attachment): RedirectResponse
    {
        abort_unless($attachment->task_id === $task->id, 404);
        $this->authorize('delete', $attachment);
        $this->attachments->delete($attachment->load('task'), request()->user());

        return back()->with('success', 'Attachment deleted.');
    }
}
