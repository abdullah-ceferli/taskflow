<?php

namespace Modules\Projects\Database\Factories;

use Modules\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['draft', 'active', 'completed', 'archived']),
            'owner_id' => User::factory(),
            'starts_at' => $this->faker->dateTime(),
            'due_at' => $this->faker->dateTime(),
        ];
    }

    public function active(): self
    {
        return $this->state(['status' => 'active']);
    }

    public function draft(): self
    {
        return $this->state(['status' => 'draft']);
    }

    public function archived(): self
    {
        return $this->state(['status' => 'archived']);
    }
}
