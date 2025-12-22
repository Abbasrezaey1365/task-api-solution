<?php

namespace App\Services;

use App\Models\Task;
use App\Notifications\TaskUpdatedNotification;
use App\Repositories\TaskRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class TaskService
{
    public function __construct(
        private readonly TaskRepository $tasks
    ) {}

    public function list(int $userId, int $projectId, array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $versionKey = $this->versionKey($userId, $projectId);
        $ver = (int) Cache::get($versionKey, 0);

        $page = (int) request()->query('page', 1);

        $cacheKey = 'tasks:list:u' . $userId
            . ':p' . $projectId
            . ':v' . $ver
            . ':pp' . $perPage
            . ':pg' . $page
            . ':f' . md5(json_encode($filters));

        return Cache::remember($cacheKey, 60, function () use ($userId, $projectId, $filters, $perPage) {
            return $this->tasks->paginateForUserProject(
                userId: $userId,
                projectId: $projectId,
                filters: $filters,
                perPage: $perPage
            );
        });
    }

    public function create(int $userId, int $projectId, array $data): Task
    {
        $data['user_id'] = $userId;

        $task = $this->tasks->create($projectId, $data);

        $this->bumpVersion($userId, $projectId);

        return $task;
    }

    public function get(int $userId, int $taskId): Task
    {
        return $this->tasks->findForUserOrFail($userId, $taskId);
    }

    public function update(int $userId, int $taskId, array $data): Task
    {
        $task = $this->tasks->findForUserOrFail($userId, $taskId);

        $oldStatus = (string) ($task->status ?? '');

        $updated = $this->tasks->update($task, $data);

        $this->bumpVersion($userId, $task->project_id);

        // Notify only when status actually changes (and status was part of request)
        if (array_key_exists('status', $data)) {
            $newStatus = (string) ($updated->status ?? '');
            if ($oldStatus !== $newStatus) {

                // notify task owner
                $task->user?->notify(new TaskUpdatedNotification($updated));

                // notify assigned_user_id (if exists and different than owner)
                if (!empty($task->assigned_user_id) && (int)$task->assigned_user_id !== (int)$task->user_id) {
                    $task->assignedUser?->notify(new TaskUpdatedNotification($updated));
                }

                // optional: notify assignee_id too (if you actually use it)
                if (!empty($task->assignee_id) && (int)$task->assignee_id !== (int)$task->user_id) {
                    $task->assignee?->notify(new TaskUpdatedNotification($updated));
                }
            }
        }

        return $updated;
    }

    public function delete(int $userId, int $taskId): void
    {
        $task = $this->tasks->findForUserOrFail($userId, $taskId);

        $projectId = (int) $task->project_id;

        $this->tasks->delete($task);

        $this->bumpVersion($userId, $projectId);
    }

    private function versionKey(int $userId, int $projectId): string
    {
        return 'tasks:list:ver:u' . $userId . ':p' . $projectId;
    }

    private function bumpVersion(int $userId, int $projectId): void
    {
        $key = $this->versionKey($userId, $projectId);

        $current = (int) Cache::get($key, 0);
        Cache::put($key, $current + 1);
    }
}
