<?php

namespace App\Services;

use App\Models\Comment;
use App\Repositories\CommentRepository;
use App\Repositories\TaskRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentService
{
    public function __construct(
        private readonly CommentRepository $comments,
        private readonly TaskRepository $tasks
    ) {
    }

    public function list(int $userId, int $taskId, int $perPage): LengthAwarePaginator
    {
        $this->tasks->findForUserOrFail($userId, $taskId);
        return $this->comments->paginateForUserTask($userId, $taskId, $perPage);
    }

    public function create(int $userId, int $taskId, array $data): Comment
    {
        $this->tasks->findForUserOrFail($userId, $taskId);

        return $this->comments->create([
            'task_id' => $taskId,
            'user_id' => $userId,
            'body' => $data['body'],
        ]);
    }

    public function update(int $userId, int $commentId, array $data): Comment
    {
        $comment = $this->comments->findForUserOrFail($userId, $commentId);
        return $this->comments->update($comment, $data);
    }

    public function delete(int $userId, int $commentId): void
    {
        $comment = $this->comments->findForUserOrFail($userId, $commentId);
        $this->comments->delete($comment);
    }
}
