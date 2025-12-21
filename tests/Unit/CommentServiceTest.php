<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\CommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeTaskFor(User $user): Task
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

    public function test_service_creates_updates_and_deletes_comment(): void
    {
        $user = User::factory()->create();
        $task = $this->makeTaskFor($user);

        /** @var CommentService $service */
        $service = $this->app->make(CommentService::class);

        $created = $service->create($user->id, $task->id, [
            'body' => 'Hello',
        ]);

        $this->assertInstanceOf(Comment::class, $created);
        $this->assertSame($user->id, $created->user_id);
        $this->assertSame($task->id, $created->task_id);

        $updated = $service->update($user->id, $created->id, [
            'body' => 'Updated',
        ]);

        $this->assertSame('Updated', $updated->body);

        $service->delete($user->id, $created->id);

        $this->assertDatabaseMissing('comments', ['id' => $created->id]);
    }
}
