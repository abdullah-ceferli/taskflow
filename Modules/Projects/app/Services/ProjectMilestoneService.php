<?php

namespace Modules\Projects\Services;

use App\Exceptions\DomainRuleViolation;
use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Activity\Enums\ActivityEvent;
use Modules\Activity\Services\ActivityRecorder;
use Modules\Projects\Contracts\ProjectAccessInterface;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectMilestone;
use Modules\Tasks\Enums\TaskStatus;

final class ProjectMilestoneService
{
    public function __construct(private readonly ProjectAccessInterface $projects, private readonly ActivityRecorder $activity) {}

    public function forProject(Project $project): Collection
    {
        return $project->milestones()->withCount(['tasks', 'tasks as done_tasks_count' => fn ($tasks) => $tasks->where('status', TaskStatus::Done->value)])->orderBy('due_at')->get();
    }

    public function create(Project $project, User $actor, string $name, ?string $description, string $dueAt): ProjectMilestone
    {
        if (! $this->projects->forActor($project->id, $actor)->manager) {
            throw new DomainRuleViolation('Only a project manager may create milestones.');
        }

        $milestone = $project->milestones()->create(['name' => trim($name), 'description' => $description, 'due_at' => $dueAt]);
        $this->activity->record(ActivityEvent::MilestoneCreated, $actor, $milestone, ['project_id' => $project->id, 'milestone_id' => $milestone->id, 'name' => $milestone->name]);

        return $milestone;
    }

    public function complete(ProjectMilestone $milestone, User $actor): ProjectMilestone
    {
        if (! $this->projects->forActor($milestone->project_id, $actor)->manager) {
            throw new DomainRuleViolation('The milestone cannot be completed.');
        }
        $milestone->update(['completed_at' => now()]);

        return $milestone;
    }
}
