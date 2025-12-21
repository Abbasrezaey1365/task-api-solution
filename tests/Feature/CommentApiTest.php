<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentApiTest extends TestCase
{
    use RefreshDatabase;

    private function setupTask(User $user): Task
    {
        $project = Project::query()->create([
            'user_id' => $user->id,
            'name' => 'P1',
            'description' => null,
        ]);

        return Task::query()->create([
            'project_id' => $project->id,
            'assigned_user_id' => null,
            'title' => 'T1',
            'description' => null,
            'status' => 'todo',
            'due_date' => null,
        ]);
    }

    public function test_user_can_create_and_list_comments(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = $this->setupTask($user);

        $create = $this->postJson("/api/tasks/{$task->id}/comments", [
            'body' => 'Hello',
        ]);

        $create->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'task_id', 'user_id', 'body']]);

        $list = $this->getJson("/api/tasks/{$task->id}/comments?per_page=10");
        $list->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_user_can_update_and_delete_comment(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = $this->setupTask($user);

        $comment = Comment::query()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'body' => 'Old',
        ]);

        $this->patchJson("/api/comments/{$comment->id}", [
            'body' => 'New body',
        ])->assertStatus(200)
            ->assertJsonPath('data.body', 'New body');

        $this->deleteJson("/api/comments/{$comment->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.success', true);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }
}
