<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskUpdatedNotification;

class TaskNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigning_task_sends_assigned_notification(): void
    {
        Notification::fake();

        $creator = User::factory()->create();
        $assignee = User::factory()->create();

        Sanctum::actingAs($creator);

        $task = Task::factory()->create([
            'assignee_id' => $creator->id,
        ]);

        $this->patchJson("/api/tasks/{$task->id}", [
            'assignee_id' => $assignee->id,
        ])->assertStatus(200);

        Notification::assertSentTo($assignee, TaskAssignedNotification::class);
    }

    public function test_updating_task_sends_updated_notification_to_assignee(): void
    {
        Notification::fake();

        $creator = User::factory()->create();
        $assignee = User::factory()->create();

        Sanctum::actingAs($creator);

        $task = Task::factory()->create([
            'assignee_id' => $assignee->id,
            'title' => 'Old',
            'status' => 'todo',
        ]);

        $this->patchJson("/api/tasks/{$task->id}", [
            'title' => 'New',
            'status' => 'doing',
        ])->assertStatus(200);

        Notification::assertSentTo($assignee, TaskUpdatedNotification::class);
    }
}
