<?php

namespace Modules\Tasks\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskCommentQueryService;
use Modules\Tasks\Services\TaskCommentService;

final class TaskCommentForm extends Component
{
    use AuthorizesRequests;

    public Task $task;

    public string $body = '';

    public function mount(Task $task): void
    {
        $this->task = $task->load('project');
        $this->authorize('comment', $this->task);
    }

    public function save(TaskCommentService $service): void
    {
        $this->authorize('comment', $this->task);
        $data = $this->validate(['body' => ['required', 'string', 'max:5000']]);
        $service->create($this->task, request()->user(), $data['body']);
        $this->reset('body');
    }

    public function render(TaskCommentQueryService $comments)
    {
        return view('tasks::livewire.task-comment-form', ['comments' => $comments->forTask($this->task)]);
    }
}
