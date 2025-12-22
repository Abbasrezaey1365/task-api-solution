<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_task_assigned_notification_when_assignee_changes(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $assignee = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $owner->id,
            'assigned_user_id' => null,
        ]);

        // Change assignee -> should notify with TaskAssignedNotification
        $task->assigned_user_id = $assignee->id;
        $task->save();

        Notification::assertSentTo($assignee, TaskAssignedNotification::class);
    }

    public function test_sends_task_updated_notification_when_task_fields_change(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $assignee = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $owner->id,
            'assigned_user_id' => $assignee->id,
            'status' => 'todo',
        ]);

        // Change a watched field -> should notify with TaskUpdatedNotification
        $task->status = 'done';
        $task->save();

        Notification::assertSentTo($assignee, TaskUpdatedNotification::class);
    }

    public function test_logs_warning_if_assigned_user_relation_is_missing(): void
    {
        Notification::fake();
        Log::spy();

        $owner = User::factory()->create();

        // assigned_user_id points to a non-existing user
        $task = Task::factory()->create([
            'user_id' => $owner->id,
            'assigned_user_id' => 999999,
            'status' => 'todo',
        ]);

        $task->status = 'done';
        $task->save();

        Notification::assertNothingSent();
        Log::shouldHaveReceived('warning')->atLeast()->once();
    }
}
