<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CommentService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $service)
    {
    }

    public function index(Request $request, int $task)
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        return response()->json([
            'data' => $this->service->list($request->user()->id, $task, $perPage),
        ]);
    }

    public function store(Request $request, int $task)
    {
        $data = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $comment = $this->service->create($request->user()->id, $task, $data);

        return response()->json([
            'data' => $comment,
        ], 201);
    }

    public function update(Request $request, int $comment)
    {
        $data = $request->validate([
            'body' => ['required', 'string'],
        ]);

        return response()->json([
            'data' => $this->service->update($request->user()->id, $comment, $data),
        ]);
    }

    public function destroy(Request $request, int $comment)
    {
        $this->service->delete($request->user()->id, $comment);

        return response()->json([
            'data' => ['success' => true],
        ]);
    }
}
