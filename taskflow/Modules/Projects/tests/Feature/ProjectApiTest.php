<?php

use App\Models\User;
use Modules\Projects\Models\Project;

describe('Projects API', function () {
    test('authenticated user can list projects', function () {
        $user = User::factory()->create();
        Project::factory(5)->create();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson(route('api.projects.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'status', 'owner_id'],
                ],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    });

    test('unauthenticated user cannot access API', function () {
        $response = $this->getJson(route('api.projects.index'));

        $response->assertUnauthorized();
    });

    test('user can create project via API', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson(route('api.projects.store'), [
                'name' => 'API Project',
                'slug' => 'api-project',
                'description' => 'Created via API',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'API Project');

        expect(Project::where('name', 'API Project')->exists())->toBeTrue();
    });

    test('user can view single project', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson(route('api.projects.show', $project));

        $response->assertOk()
            ->assertJsonPath('data.id', $project->id)
            ->assertJsonPath('data.name', $project->name);
    });

    test('user cannot view project they dont own', function () {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $response = $this
            ->actingAs($viewer, 'sanctum')
            ->getJson(route('api.projects.show', $project));

        $response->assertForbidden();
    });
});
