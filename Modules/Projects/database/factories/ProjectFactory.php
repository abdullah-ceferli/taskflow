<?php

namespace Modules\Projects\Database\Factories;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;

/** @extends Factory<Project> */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $name = fake()->unique()->sentence(3);

        return [
            'workspace_id' => null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('####'),
            'description' => fake()->optional()->paragraph(),
            'status' => ProjectStatus::Active,
            'owner_id' => User::factory(),
            'starts_at' => today(),
            'due_at' => today()->addDays(14),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Project $project): void {
            if ($project->workspace_id) {
                return;
            }

            $workspaceId = WorkspaceMember::query()->where('user_id', $project->owner_id)->value('workspace_id');
            if (! $workspaceId) {
                $workspace = Workspace::factory()->create(['owner_id' => $project->owner_id]);
                WorkspaceMember::query()->create([
                    'workspace_id' => $workspace->id,
                    'user_id' => $project->owner_id,
                    'role' => WorkspaceRole::Owner,
                    'joined_at' => now(),
                ]);
                $workspaceId = $workspace->id;
            }

            $project->workspace_id = $workspaceId;
        })->afterCreating(function (Project $project): void {
            WorkspaceMember::query()->firstOrCreate(
                ['workspace_id' => $project->workspace_id, 'user_id' => $project->owner_id],
                ['role' => WorkspaceRole::Owner, 'joined_at' => now()],
            );
        });
    }

    public function archived(): static
    {
        return $this->state(fn (): array => ['status' => ProjectStatus::Archived]);
    }
}
