<?php

namespace Modules\Tasks\Services;

use App\Exceptions\DomainRuleViolation;
use App\Models\User;
use App\Services\CurrentWorkspace;
use Illuminate\Support\Collection;
use Modules\Projects\Models\Project;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskLabel;

final class TaskLabelService
{
    public function __construct(private readonly CurrentWorkspace $current) {}

    /** @return Collection<int, TaskLabel> */
    public function list(User $actor): Collection
    {
        return TaskLabel::query()->where('workspace_id', $this->current->idFor($actor))->orderBy('name')->get();
    }

    public function create(User $actor, string $name, string $color, ?int $projectId): TaskLabel
    {
        if (! $this->current->canManage($actor)) {
            throw new DomainRuleViolation('Only workspace owners and managers can create labels.');
        }

        $workspaceId = $this->current->idFor($actor) ?? throw new DomainRuleViolation('A current workspace is required.');
        if ($projectId && ! Project::query()->inCurrentWorkspace($actor)->whereKey($projectId)->exists()) {
            throw new DomainRuleViolation('The label project must belong to the current workspace.');
        }

        return TaskLabel::query()->firstOrCreate(
            ['workspace_id' => $workspaceId, 'project_id' => $projectId, 'name' => trim($name)],
            ['color' => $color],
        );
    }

    public function sync(Task $task, User $actor, array $labelIds): Task
    {
        $ids = array_values(array_unique(array_map('intval', $labelIds)));
        $labels = TaskLabel::query()
            ->where('workspace_id', $this->current->idFor($actor))
            ->whereIn('id', $ids)
            ->where(fn ($query) => $query->whereNull('project_id')->orWhere('project_id', $task->project_id))
            ->pluck('id');

        if ($labels->count() !== count($ids)) {
            throw new DomainRuleViolation('One or more labels are outside the task workspace or project.');
        }

        $task->labels()->sync($labels);

        return $task->load('labels');
    }
}
