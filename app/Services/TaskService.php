<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class TaskService
{
    public function __construct(
        private readonly TaskRepository $tasks,
        private readonly ProjectRepository $projects
    ) {}

    public function list(int $userId, int $projectId, array $filters, int $perPage): LengthAwarePaginator
    {
        // ensure project belongs to user
        $this->projects->findForUserOrFail($userId, $projectId);

        $versionKey = $this->versionKey($userId, $projectId);

        // ✅ IMPORTANT: default must be 0, not 1
        $version = (int) Cache::get($versionKey, 0);

        $cacheKey = 'tasks:list:' . $version . ':u' . $userId . ':p' . $projectId . ':' . md5(json_encode([
            'filters' => $filters,
            'perPage' => $perPage,
            'page' => request()->query('page', 1),
        ]));

        return Cache::remember($cacheKey, now()->addSeconds(60), function () use ($userId, $projectId, $filters, $perPage) {
            return $this->tasks->paginateForUserProject($userId, $projectId, $filters, $perPage);
        });
    }

    public function create(int $userId, int $projectId, array $data): Task
    {
        $this->projects->findForUserOrFail($userId, $projectId);

        $task = $this->tasks->create([
            'project_id' => $projectId,
            'assigned_user_id' => $data['assigned_user_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'todo',
            'due_date' => $data['due_date'] ?? null,
        ]);

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

        $updated = $this->tasks->update($task, $data);

        $this->bumpVersion($userId, $task->project_id);

        return $updated;
    }

    public function delete(int $userId, int $taskId): void
    {
        $task = $this->tasks->findForUserOrFail($userId, $taskId);
        $projectId = $task->project_id;

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

        // ✅ ensure key exists first (so increment always changes the number)
        Cache::add($key, 0);

        Cache::increment($key);
    }
}
