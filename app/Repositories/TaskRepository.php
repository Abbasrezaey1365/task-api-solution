<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskRepository
{
    public function paginateForUserProject(
        int $userId,
        int $projectId,
        array $filters,
        int $perPage
    ): LengthAwarePaginator {
        $q = Task::query()
            ->where('project_id', $projectId)
            ->whereHas('project', fn ($p) => $p->where('user_id', $userId))
            ->latest();

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        if (!empty($filters['due_after'])) {
            $q->whereDate('due_date', '>=', $filters['due_after']);
        }

        if (!empty($filters['due_before'])) {
            $q->whereDate('due_date', '<=', $filters['due_before']);
        }

        if (!empty($filters['q'])) {
            $term = trim((string) $filters['q']);
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        return $q->paginate($perPage);
    }

    public function create(array $data): Task
    {
        return Task::query()->create($data);
    }

    public function findForUserOrFail(int $userId, int $taskId): Task
    {
        return Task::query()
            ->where('id', $taskId)
            ->whereHas('project', fn ($p) => $p->where('user_id', $userId))
            ->firstOrFail();
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
