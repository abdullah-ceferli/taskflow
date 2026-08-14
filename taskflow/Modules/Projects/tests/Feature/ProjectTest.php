<?php

use App\Models\User;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectMember;

describe('Projects Authorization', function () {
    test('user can create project', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('projects.store'), [
                'name' => 'Test Project',
                'slug' => 'test-project',
                'description' => 'Test Description',
            ]);

        $response->assertRedirect();
        expect(Project::where('name', 'Test Project')->exists())->toBeTrue();
    });

    test('user cannot view project they dont own', function () {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $response = $this
            ->actingAs($viewer)
            ->get(route('projects.show', $project));

        $response->assertForbidden();
    });

    test('project owner can update their project', function () {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $response = $this
            ->actingAs($owner)
            ->put(route('projects.update', $project), [
                'name' => 'Updated Name',
            ]);

        $response->assertRedirect();
        $project->refresh();
        expect($project->name)->toBe('Updated Name');
    });

    test('non-owner cannot update project', function () {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $response = $this
            ->actingAs($other)
            ->put(route('projects.update', $project), [
                'name' => 'Hacked',
            ]);

        $response->assertForbidden();
    });

    test('only owner can archive project', function () {
        $owner = User::factory()->create();
        $project = Project::factory()->active()->create(['owner_id' => $owner->id]);

        $response = $this
            ->actingAs($owner)
            ->post(route('projects.archive', $project));

        $response->assertRedirect();
        $project->refresh();
        expect($project->status)->toBe('archived');
    });
});

describe('Project Members', function () {
    test('owner can add member', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $response = $this
            ->actingAs($owner)
            ->post(route('projects.members.store', $project), [
                'user_id' => $member->id,
                'member_role' => 'member',
            ]);

        $response->assertRedirect();
        expect(ProjectMember::where('project_id', $project->id)->where('user_id', $member->id)->exists())->toBeTrue();
    });

    test('cannot add duplicate member', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        ProjectMember::factory()->create([
            'project_id' => $project->id,
            'user_id' => $member->id,
        ]);

        $this->expectException(Exception::class);
        $this
            ->actingAs($owner)
            ->post(route('projects.members.store', $project), [
                'user_id' => $member->id,
                'member_role' => 'member',
            ]);
    });

    test('only owner can remove members', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $projectMember = ProjectMember::factory()->create([
            'project_id' => $project->id,
            'user_id' => $member->id,
        ]);

        $response = $this
            ->actingAs($owner)
            ->delete(route('projects.members.destroy', [$project, $member->id]));

        $response->assertRedirect();
        expect(ProjectMember::find($projectMember->id))->toBeNull();
    });
});
