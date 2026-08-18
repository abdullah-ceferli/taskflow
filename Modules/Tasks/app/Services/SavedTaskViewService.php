<?php

namespace Modules\Tasks\Services;

use App\Exceptions\DomainRuleViolation;
use App\Models\User;
use App\Services\CurrentWorkspace;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Modules\Tasks\Models\SavedTaskView;

final class SavedTaskViewService
{
    private const FILTER_KEYS = ['search', 'status', 'priority', 'project_id', 'assignee_id', 'due_before', 'sort', 'direction', 'label_ids'];

    public function __construct(private readonly CurrentWorkspace $current) {}

    /** @return Collection<int, SavedTaskView> */
    public function list(User $actor): Collection
    {
        return SavedTaskView::query()->where('workspace_id', $this->current->idFor($actor))->where('user_id', $actor->id)->orderBy('name')->get();
    }

    public function create(User $actor, string $name, array $filters): SavedTaskView
    {
        $workspaceId = $this->current->idFor($actor) ?? throw new DomainRuleViolation('A current workspace is required.');

        return SavedTaskView::query()->updateOrCreate(
            ['workspace_id' => $workspaceId, 'user_id' => $actor->id, 'name' => trim($name)],
            ['filters' => Arr::only($filters, self::FILTER_KEYS)],
        );
    }

    public function delete(User $actor, SavedTaskView $view): void
    {
        if ($view->user_id !== $actor->id || $view->workspace_id !== $this->current->idFor($actor)) {
            throw new DomainRuleViolation('This saved view does not belong to you.');
        }

        $view->delete();
    }
}
