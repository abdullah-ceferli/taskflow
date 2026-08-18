<?php

namespace Modules\Tasks\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Modules\Projects\Models\Project;
use Modules\Tasks\Data\CreateTaskData;
use Modules\Tasks\Enums\TaskPriority;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskQueryService;
use Modules\Tasks\Services\TaskService;

final class QuickTaskCreate extends Component
{
    use AuthorizesRequests;

    public string $projectId = '';

    public string $title = '';

    public string $priority = 'medium';

    public function save(TaskService $service): void
    {
        $data = $this->validate([
            'projectId' => ['required', 'integer'],
            'title' => ['required', 'string', 'min:3', 'max:180'],
            'priority' => ['required'],
        ]);

        $project = Project::query()->findOrFail((int) $data['projectId']);
        $this->authorize('create', [Task::class, $project]);

        $service->create(request()->user(), $project, new CreateTaskData(
            $project->id, $data['title'], null, null, TaskPriority::from($data['priority']), null,
        ));

        $this->reset('title');
        session()->flash('success', 'Task created successfully.');
    }

    public function render(TaskQueryService $tasks)
    {
        return view('tasks::livewire.quick-task-create', [
            'projects' => $tasks->projects(request()->user()),
            'priorities' => TaskPriority::cases(),
        ]);
    }
}
