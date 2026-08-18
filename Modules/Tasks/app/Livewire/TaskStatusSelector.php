<?php

namespace Modules\Tasks\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskStatusService;

final class TaskStatusSelector extends Component
{
    use AuthorizesRequests;

    public Task $task;

    public string $status = '';

    public function mount(Task $task, TaskStatusService $service): void
    {
        $this->task = $task->load('project');
        $this->authorize('changeStatus', $this->task);
        $this->status = $service->availableStatuses($this->task, request()->user())[0]->value ?? '';
    }

    public function change(TaskStatusService $service): void
    {
        $this->authorize('changeStatus', $this->task);
        $this->validate(['status' => ['required']]);
        $this->task = $service->change($this->task, TaskStatus::from($this->status), request()->user());
        $this->status = $service->availableStatuses($this->task->load('project'), request()->user())[0]->value ?? '';
    }

    public function render(TaskStatusService $service)
    {
        return view('tasks::livewire.task-status-selector', [
            'available' => $service->availableStatuses($this->task->load('project'), request()->user()),
        ]);
    }
}
