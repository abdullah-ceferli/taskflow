<?php

namespace Modules\Tasks\Database\Factories;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\WorkspaceMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Projects\Models\Project;
use Modules\Tasks\Enums\TaskPriority;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Models\Task;

/** @extends Factory<Task> */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'number' => 'TF-'.fake()->unique()->numerify('######'),
            'project_id' => Project::factory(),
            'creator_id' => User::factory(),
            'assignee_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Medium,
            'due_at' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (): array => ['assignee_id' => $user->id]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Task $task): void {
            if (! $task->assignee_id) {
                return;
            }

            WorkspaceMember::query()->firstOrCreate(
                ['workspace_id' => $task->project->workspace_id, 'user_id' => $task->assignee_id],
                ['role' => WorkspaceRole::Member, 'joined_at' => now()],
            );
        });
    }
}
