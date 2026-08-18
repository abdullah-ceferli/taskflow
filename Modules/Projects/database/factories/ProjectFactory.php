<?php

namespace Modules\Projects\Database\Factories;

use App\Models\User;
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
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('####'),
            'description' => fake()->optional()->paragraph(),
            'status' => ProjectStatus::Active,
            'owner_id' => User::factory(),
            'starts_at' => today(),
            'due_at' => today()->addDays(14),
        ];
    }

    public function archived(): static
    {
        return $this->state(fn (): array => ['status' => ProjectStatus::Archived]);
    }
}
