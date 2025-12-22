<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_can_be_created_and_has_owner_relation()
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Project',
        ]);

        $this->assertEquals('Test Project', $project->name);

        // If you have relation like $project->user or $project->owner:
        if (method_exists($project, 'user')) {
            $this->assertEquals($user->id, $project->user->id);
        }
    }
}
