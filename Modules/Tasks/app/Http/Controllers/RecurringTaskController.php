<?php

namespace Modules\Tasks\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Projects\Models\Project;
use Modules\Tasks\Data\CreateRecurringTaskData;
use Modules\Tasks\Enums\RecurrenceFrequency;
use Modules\Tasks\Enums\TaskPriority;
use Modules\Tasks\Http\Requests\StoreRecurringTaskRequest;
use Modules\Tasks\Models\RecurringTask;
use Modules\Tasks\Services\RecurringTaskService;

final class RecurringTaskController
{
    use AuthorizesRequests;

    public function index(Project $project): View
    {
        $this->authorize('view', $project);

        return view('tasks::recurring.index', ['project' => $project->load(['milestones', 'members']), 'recurringTasks' => $project->recurringTasks()->latest()->get(), 'frequencies' => RecurrenceFrequency::cases(), 'priorities' => TaskPriority::cases()]);
    }

    public function store(StoreRecurringTaskRequest $request, Project $project, RecurringTaskService $service): RedirectResponse
    {
        $this->authorize('update', $project);
        $service->create($project, $request->user(), CreateRecurringTaskData::fromArray($request->validated()));

        return back()->with('success', 'Recurring task created.');
    }

    public function destroy(Project $project, RecurringTask $recurringTask, RecurringTaskService $service): RedirectResponse
    {
        abort_unless($recurringTask->project_id === $project->id, 404);
        $this->authorize('update', $project);
        $service->deactivate($recurringTask, request()->user());

        return back()->with('success', 'Recurring task stopped.');
    }
}
