<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_creates_and_gets_project_for_user(): void
    {
        $user = User::factory()->create();

        /** @var ProjectService $service */
        $service = $this->app->make(ProjectService::class);

        $created = $service->create($user->id, [
            'name' => 'My Project',
            'description' => 'Desc',
        ]);

        $this->assertInstanceOf(Project::class, $created);
        $this->assertSame($user->id, $created->user_id);
        $this->assertSame('My Project', $created->name);

        $fetched = $service->get($user->id, $created->id);

        $this->assertSame($created->id, $fetched->id);
        $this->assertSame($user->id, $fetched->user_id);
    }

    public function test_service_updates_and_deletes_project_for_user(): void
    {
        $user = User::factory()->create();

        $project = Project::query()->create([
            'user_id' => $user->id,
            'name' => 'Old',
            'description' => null,
        ]);

        /** @var ProjectService $service */
        $service = $this->app->make(ProjectService::class);

        $updated = $service->update($user->id, $project->id, [
            'name' => 'New',
            'description' => 'New desc',
        ]);

        $this->assertSame('New', $updated->name);
        $this->assertSame('New desc', $updated->description);

        $service->delete($user->id, $project->id);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
