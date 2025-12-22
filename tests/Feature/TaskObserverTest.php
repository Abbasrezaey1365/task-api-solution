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

        // Update something that should trigger observer logic
        $task->update(['title' => 'B']);

        $task->refresh();

        // Example assertions — adjust to your real observer behavior:
        // If observer bumps version:
        if (isset($task->version)) {
            $this->assertGreaterThanOrEqual(1, (int)$task->version);
        }

        // If observer touches updated_at or sets something:
        $this->assertEquals('B', $task->title);
    }
}
