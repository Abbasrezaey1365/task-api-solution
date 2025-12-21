<?php

namespace App\Repositories;

use App\Models\Comment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentRepository
{
    public function paginateForUserTask(int $userId, int $taskId, int $perPage): LengthAwarePaginator
    {
        return Comment::query()
            ->where('task_id', $taskId)
            ->whereHas('task.project', fn ($p) => $p->where('user_id', $userId))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Comment
    {
        return Comment::query()->create($data);
    }

    public function findForUserOrFail(int $userId, int $commentId): Comment
    {
        return Comment::query()
            ->where('id', $commentId)
            ->whereHas('task.project', fn ($p) => $p->where('user_id', $userId))
            ->firstOrFail();
    }

    public function update(Comment $comment, array $data): Comment
    {
        $comment->update($data);
        return $comment->refresh();
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}
