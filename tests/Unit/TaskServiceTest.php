<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_bumps_version_and_refreshes_cached_list(): void
    {
        $user = User::factory()->create();

        $project = Project::query()->create([
            'user_id' => $user->id,
            'name' => 'P1',
            'description' => null,
        ]);

        $service = $this->app->make(TaskService::class);

        $page1 = $service->list(
            userId: $user->id,
            projectId: $project->id,
            filters: [],
            perPage: 50
        );
        $this->assertCount(0, $page1->items());


        Task::query()->create([
            'project_id' => $project->id,
            'assigned_user_id' => null,
            'title' => 'Direct Insert',
            'description' => null,
            'status' => 'todo',
            'due_date' => null,
        ]);


        $page2 = $service->list(
            userId: $user->id,
            projectId: $project->id,
            filters: [],
            perPage: 50
        );
        $this->assertCount(0, $page2->items());


        $created = $service->create(
            userId: $user->id,
            projectId: $project->id,
            data: [
                'title' => 'Service Create',
                'description' => 'Desc',
                'status' => 'todo',
            ]
        );
        $this->assertSame('Service Create', $created->title);


        $versionKey = 'tasks:list:ver:u' . $user->id . ':p' . $project->id;
        $this->assertGreaterThanOrEqual(1, (int) Cache::get($versionKey, 0));

 
        $page3 = $service->list(
            userId: $user->id,
            projectId: $project->id,
            filters: [],
            perPage: 50
        );
        $this->assertCount(2, $page3->items());
    }
}
