<?php

namespace Modules\Tasks\Services;

use App\Exceptions\DomainRuleViolation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Modules\Activity\Enums\ActivityEvent;
use Modules\Activity\Services\ActivityRecorder;
use Modules\Projects\Contracts\ProjectAccessInterface;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectMilestone;
use Modules\Tasks\Data\CreateRecurringTaskData;
use Modules\Tasks\Data\CreateTaskData;
use Modules\Tasks\Models\RecurringTask;
use Modules\Tasks\Models\RecurringTaskOccurrence;
use Modules\Tasks\Models\Task;

final class RecurringTaskService
{
    public function __construct(
        private readonly ProjectAccessInterface $projects,
        private readonly TaskService $tasks,
        private readonly RecurrenceCalculator $calculator,
        private readonly ActivityRecorder $activity,
    ) {}

    public function create(Project $project, User $actor, CreateRecurringTaskData $data): RecurringTask
    {
        $access = $this->projects->forActor($project->id, $actor);
        if (! $access->active || ! $access->manager) {
            throw new DomainRuleViolation('Recurring tasks require an active managed project.');
        }
        $this->validateTemplateLinks($project, $data->assigneeId, $data->milestoneId);

        return RecurringTask::query()->create([
            'workspace_id' => $project->workspace_id,
            'project_id' => $project->id,
            'created_by' => $actor->id,
            'assignee_id' => $data->assigneeId,
            'milestone_id' => $data->milestoneId,
            'title' => $data->title,
            'description' => $data->description,
            'priority' => $data->priority,
            'estimate_hours' => $data->estimateHours,
            'frequency' => $data->frequency,
            'interval' => $data->interval,
            'timezone' => $data->timezone,
            'due_offset_days' => $data->dueOffsetDays,
            'next_run_at' => CarbonImmutable::instance($data->startsAt)->utc(),
            'active' => true,
        ]);
    }

    public function deactivate(RecurringTask $recurringTask, User $actor): void
    {
        if (! $this->projects->forActor($recurringTask->project_id, $actor)->manager) {
            throw new DomainRuleViolation('The recurring task cannot be changed.');
        }

        $recurringTask->update(['active' => false]);
    }

    public function generate(int $recurringTaskId): ?Task
    {
        return DB::transaction(function () use ($recurringTaskId): ?Task {
            $recurring = RecurringTask::query()->with(['creator', 'project'])->lockForUpdate()->findOrFail($recurringTaskId);
            if (! $recurring->active || $recurring->next_run_at->isFuture()) {
                return null;
            }

            $scheduledFor = $recurring->next_run_at->toImmutable();
            $occurrence = RecurringTaskOccurrence::query()->firstOrCreate([
                'recurring_task_id' => $recurring->id,
                'scheduled_for' => $scheduledFor,
            ]);
            if ($occurrence->task_id) {
                return $occurrence->task;
            }

            $task = $this->tasks->create($recurring->creator, $recurring->project_id, new CreateTaskData(
                $recurring->project_id,
                $recurring->title,
                $recurring->description,
                $recurring->assignee_id,
                $recurring->priority,
                $scheduledFor->addDays($recurring->due_offset_days)->toDateTimeImmutable(),
                $recurring->milestone_id,
                (float) $recurring->estimate_hours,
            ));
            $occurrence->update(['task_id' => $task->id]);
            $recurring->update([
                'last_generated_at' => now(),
                'next_run_at' => $this->calculator->next($scheduledFor, $recurring->frequency, $recurring->interval, $recurring->timezone),
            ]);
            $this->activity->record(ActivityEvent::RecurringTaskGenerated, $recurring->creator, $task, ['project_id' => $task->project_id, 'task_id' => $task->id, 'recurring_task_id' => $recurring->id, 'scheduled_for' => $scheduledFor->toISOString()]);

            return $task;
        });
    }

    private function validateTemplateLinks(Project $project, ?int $assigneeId, ?int $milestoneId): void
    {
        if ($assigneeId && ! $project->members()->whereKey($assigneeId)->exists() && $project->owner_id !== $assigneeId) {
            throw new DomainRuleViolation('The recurring assignee must be a project member.');
        }
        if ($milestoneId && ! ProjectMilestone::query()->whereKey($milestoneId)->where('project_id', $project->id)->exists()) {
            throw new DomainRuleViolation('The recurring milestone must belong to the project.');
        }
    }
}
