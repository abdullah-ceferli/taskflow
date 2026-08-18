<?php

namespace Modules\Tasks\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Tasks\Data\TaskFiltersData;
use Modules\Tasks\Enums\TaskPriority;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskQueryService;

final class TaskFilters extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public string $status = '';

    public string $priority = '';

    public string $projectId = '';

    public string $assigneeId = '';

    public string $dueBefore = '';

    public string $sort = 'created_at';

    public string $direction = 'desc';

    /** @var list<int|string> */
    public array $labelIds = [];

    public function updated($name): void
    {
        if ($name !== 'page') {
            $this->resetPage();
        }
    }

    public function render(TaskQueryService $tasks)
    {
        $this->authorize('viewAny', Task::class);
        $actor = request()->user();
        $filters = TaskFiltersData::fromArray(['search' => $this->search, 'status' => $this->status, 'priority' => $this->priority, 'project_id' => $this->projectId ?: null, 'assignee_id' => $this->assigneeId ?: null, 'due_before' => $this->dueBefore ?: null, 'sort' => $this->sort, 'direction' => $this->direction, 'label_ids' => $this->labelIds]);

        return view('tasks::livewire.task-filters', ['tasks' => $tasks->paginate($actor, $filters), 'projects' => $tasks->projects($actor), 'users' => $tasks->users($actor), 'statuses' => TaskStatus::cases(), 'priorities' => TaskPriority::cases(), 'labels' => $tasks->labels($actor)]);
    }
}
