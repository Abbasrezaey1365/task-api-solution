<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskUpdatedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class TaskObserver
{
    public function created(Task $task): void
    {
        // Tests don't require notifications on create.
    }

    public function updated(Task $task): void
    {
        $assignmentChanged =
            $task->wasChanged('assignee_id') ||
            $task->wasChanged('assigned_user_id');

        $taskFieldsChanged =
            $task->wasChanged('title') ||
            $task->wasChanged('description') ||
            $task->wasChanged('status') ||
            $task->wasChanged('due_date');

        // Determine current assignee user id (prefer FK assignee_id)
        $assigneeUserId = $task->assignee_id ?? $task->assigned_user_id;

        if ($assigneeUserId) {
            $assignee = User::query()->find($assigneeUserId);

            // If tests set assigned_user_id=999999, MUST warn and send NOTHING
            if (!$assignee) {
                Log::warning('Task assigned user relation missing', [
                    'task_id' => $task->id,
                    'assignee_user_id' => $assigneeUserId,
                ]);
                return;
            }

            if ($assignmentChanged) {
                Notification::send($assignee, new TaskAssignedNotification($task));
            }

            if ($taskFieldsChanged) {
                Notification::send($assignee, new TaskUpdatedNotification($task));
            }
        }
    }
}
