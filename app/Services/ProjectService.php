<?php

namespace App\Services;

use App\Models\Project;
use App\Repositories\ProjectRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function __construct(private readonly ProjectRepository $projects)
    {
    }

    public function list(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->projects->paginateForUser($userId, $perPage);
    }

    public function create(int $userId, array $data): Project
    {
        return $this->projects->createForUser($userId, $data);
    }

    public function get(int $userId, int $projectId): Project
    {
        return $this->projects->findForUserOrFail($userId, $projectId);
    }

    public function update(int $userId, int $projectId, array $data): Project
    {
        $project = $this->projects->findForUserOrFail($userId, $projectId);
        return $this->projects->update($project, $data);
    }

    public function delete(int $userId, int $projectId): void
    {
        $project = $this->projects->findForUserOrFail($userId, $projectId);
        $this->projects->delete($project);
    }
}
