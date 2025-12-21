<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_list_projects(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/projects', [
            'name' => 'My Project',
            'description' => 'Desc',
        ]);

        $create->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'name', 'description'],
            ]);

        $list = $this->getJson('/api/projects?per_page=10');

        $list->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data', // paginator items
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_user_can_show_update_and_delete_own_project(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::query()->create([
            'user_id' => $user->id,
            'name' => 'Old',
            'description' => 'Old desc',
        ]);

        $show = $this->getJson("/api/projects/{$project->id}");
        $show->assertStatus(200)->assertJsonPath('data.id', $project->id);

        $update = $this->patchJson("/api/projects/{$project->id}", [
            'name' => 'New Name',
            'description' => 'New desc',
        ]);

        $update->assertStatus(200)->assertJsonPath('data.name', 'New Name');

        $delete = $this->deleteJson("/api/projects/{$project->id}");
        $delete->assertStatus(200)->assertJsonPath('data.success', true);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_user_cannot_access_other_users_project_expect_404(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();

        $project = Project::query()->create([
            'user_id' => $owner->id,
            'name' => 'Owner Project',
            'description' => null,
        ]);

        Sanctum::actingAs($attacker);

        $this->getJson("/api/projects/{$project->id}")->assertStatus(404);
        $this->patchJson("/api/projects/{$project->id}", ['name' => 'Hacked'])->assertStatus(404);
        $this->deleteJson("/api/projects/{$project->id}")->assertStatus(404);
    }
}
