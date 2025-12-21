<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Repositories\CommentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentRepositoryTest extends TestCase
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

    public function test_repository_paginates_comments_for_users_task(): void
    {
        $user = User::factory()->create();
        $task = $this->makeTaskFor($user);

        Comment::query()->create(['task_id' => $task->id, 'user_id' => $user->id, 'body' => 'C1']);
        Comment::query()->create(['task_id' => $task->id, 'user_id' => $user->id, 'body' => 'C2']);

        /** @var CommentRepository $repo */
        $repo = $this->app->make(CommentRepository::class);

        $page = $repo->paginateForUserTask($user->id, $task->id, 50);

        $this->assertSame(2, $page->total());
        $this->assertTrue(collect($page->items())->every(fn ($c) => $c->task_id === $task->id));
    }

    public function test_repository_find_for_user_or_fail(): void
    {
        $user = User::factory()->create();
        $task = $this->makeTaskFor($user);

        $comment = Comment::query()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'body' => 'Hello',
        ]);

        /** @var CommentRepository $repo */
        $repo = $this->app->make(CommentRepository::class);

        $found = $repo->findForUserOrFail($user->id, $comment->id);

        $this->assertSame($comment->id, $found->id);
        $this->assertSame($user->id, $found->user_id);
    }
}
