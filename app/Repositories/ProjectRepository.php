<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectRepository
{
    public function paginateForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Project::query()
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function createForUser(int $userId, array $data): Project
    {
        return Project::query()->create([
            'user_id' => $userId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function findForUserOrFail(int $userId, int $projectId): Project
    {
        return Project::query()
            ->where('user_id', $userId)
            ->where('id', $projectId)
            ->firstOrFail();
    }

    public function update(Project $project, array $data): Project
    {
        $project->update([
            'name' => $data['name'] ?? $project->name,
            'description' => array_key_exists('description', $data) ? $data['description'] : $project->description,
        ]);

        return $project->refresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
