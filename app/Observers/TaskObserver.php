<?php

namespace App\Observers;

use App\Models\Task;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskUpdatedNotification;

class TaskObserver
{
public function created(Task $task): void
{
    \Log::info('TaskObserver@created fired', [
        'task_id' => $task->id,
        'assigned_user_id' => $task->assigned_user_id,
    ]);

    if (!$task->assigned_user_id) {
        return;
    }

    $assignee = $task->assignedUser;
    if (!$assignee) {
        \Log::warning('TaskObserver@created: assignedUser relation returned null', [
            'task_id' => $task->id,
            'assigned_user_id' => $task->assigned_user_id,
        ]);
        return;
    }

    $assignee->notify(new TaskAssignedNotification($task));
}


    public function updated(Task $task): void
    {
        \Log::info('TaskObserver@updated fired', [
            'task_id' => $task->id,
            'changes' => $task->getChanges(),
        ]);

        // Assignment changed -> assignment notification
        if ($task->wasChanged('assigned_user_id')) {
            if (!$task->assigned_user_id) {
                return; // unassigned, no notification
            }

            $assignee = $task->assignedUser;
            if (!$assignee) {
                \Log::warning('TaskObserver@updated: assignedUser relation returned null', [
                    'task_id' => $task->id,
                    'assigned_user_id' => $task->assigned_user_id,
                ]);
                return;
            }

            $assignee->notify(new TaskAssignedNotification($task));
            return;
        }

        // Meaningful updates -> updated notification
        if ($task->wasChanged(['title', 'description', 'status', 'due_date'])) {
            if (!$task->assigned_user_id) {
                return;
            }

            $assignee = $task->assignedUser;
            if (!$assignee) {
                \Log::warning('TaskObserver@updated: assignedUser relation returned null', [
                    'task_id' => $task->id,
                    'assigned_user_id' => $task->assigned_user_id,
                ]);
                return;
            }

            $assignee->notify(new TaskUpdatedNotification($task));
        }
    }
}
