<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskUpdatedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_array_contains_task_id_and_message(): void
    {
        $owner = User::factory()->create();
        $assignee = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $owner->id,
            'assigned_user_id' => $assignee->id,
            'title' => 'My Task',
        ]);

        $n = new TaskUpdatedNotification($task);

        $data = $n->toArray($assignee);

        $this->assertArrayHasKey('task_id', $data);
        $this->assertSame($task->id, $data['task_id']);

        $this->assertArrayHasKey('message', $data);
        $this->assertIsString($data['message']);
        $this->assertNotEmpty($data['message']);
    }
}
