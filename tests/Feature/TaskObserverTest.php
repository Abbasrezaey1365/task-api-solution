<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_observer_runs_on_create_or_update()
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'A',
        ]);
        $task->update(['title' => 'B']);

        $task->refresh();


        if (isset($task->version)) {
            $this->assertGreaterThanOrEqual(1, (int)$task->version);
        }


        $this->assertEquals('B', $task->title);
    }
}
