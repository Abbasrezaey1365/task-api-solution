<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_repository_filters_by_status(): void
    {
        $user = User::factory()->create();

        $project = Project::query()->create([
            'user_id' => $user->id,
            'name' => 'P1',
            'description' => null,
        ]);

        Task::query()->create([
            'project_id' => $project->id,
            'assigned_user_id' => null,
            'title' => 'A',
            'description' => null,
            'status' => 'todo',
            'due_date' => null,
        ]);

        Task::query()->create([
            'project_id' => $project->id,
            'assigned_user_id' => null,
            'title' => 'B',
            'description' => null,
            'status' => 'done',
            'due_date' => null,
        ]);

        /** @var TaskRepository $repo */
        $repo = $this->app->make(TaskRepository::class);

        $result = $repo->paginateForUserProject(
            userId: $user->id,
            projectId: $project->id,
            filters: ['status' => 'todo'],
            perPage: 50
        );

        $items = $result->items();

        $this->assertCount(1, $items);
        $this->assertSame('todo', $items[0]->status);
        $this->assertSame('A', $items[0]->title);
    }
}
