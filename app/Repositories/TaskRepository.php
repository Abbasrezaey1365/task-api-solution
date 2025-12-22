<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskRepository
{
    public function paginateForUserProject(int $userId, int $projectId, array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $q = Task::query()
            ->where('project_id', $projectId)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('assignee_id', $userId)
                  ->orWhere('assigned_user_id', $userId)
                  ->orWhereHas('project', function ($p) use ($userId) {
                      $p->where('user_id', $userId);
                  });
            });

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        return $q->orderByDesc('id')->paginate($perPage);
    }

    public function findForUserOrFail(int $userId, int $taskId): Task
    {
        return Task::query()
            ->whereKey($taskId)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('assignee_id', $userId)
                  ->orWhere('assigned_user_id', $userId)
                  ->orWhereHas('project', function ($p) use ($userId) {
                      $p->where('user_id', $userId);
                  });
            })
            ->firstOrFail();
    }

    public function create(int $projectId, array $data): Task
    {
        return Task::query()->create([
            'project_id' => $projectId,
            'user_id' => $data['user_id'] ?? null,          // service sets this
            'assignee_id' => $data['assignee_id'] ?? null,
            'assigned_user_id' => $data['assigned_user_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'todo',
            'due_date' => $data['due_date'] ?? null,
        ]);
    }

    public function update(Task $task, array $data): Task
    {
        $task->fill([
            'title' => $data['title'] ?? $task->title,
            'description' => array_key_exists('description', $data) ? $data['description'] : $task->description,
            'status' => $data['status'] ?? $task->status,
            'due_date' => array_key_exists('due_date', $data) ? $data['due_date'] : $task->due_date,
            'assignee_id' => array_key_exists('assignee_id', $data) ? $data['assignee_id'] : $task->assignee_id,
            'assigned_user_id' => array_key_exists('assigned_user_id', $data) ? $data['assigned_user_id'] : $task->assigned_user_id,
        ]);

        $task->save();

        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
