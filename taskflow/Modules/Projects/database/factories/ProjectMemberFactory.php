<?php

namespace Modules\Projects\Database\Factories;

use Modules\Projects\Models\ProjectMember;
use Modules\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectMemberFactory extends Factory
{
    protected $model = ProjectMember::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'member_role' => $this->faker->randomElement(['member', 'manager']),
            'joined_at' => $this->faker->dateTime(),
        ];
    }

    public function manager(): self
    {
        return $this->state(['member_role' => 'manager']);
    }

    public function member(): self
    {
        return $this->state(['member_role' => 'member']);
    }
}
