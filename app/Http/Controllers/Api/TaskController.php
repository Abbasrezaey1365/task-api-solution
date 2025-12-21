<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $service)
    {
    }

    public function index(Request $request, int $project)
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['todo', 'in-progress', 'done'])],
            'due_after' => ['nullable', 'date'],
            'due_before' => ['nullable', 'date'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json([
            'data' => $this->service->list($request->user()->id, $project, $filters, $perPage),
        ]);
    }

    public function store(Request $request, int $project)
    {
$data = $request->validate([
    'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],

    'title' => ['required', 'string', 'max:255'],
    'description' => ['nullable', 'string'],
    'status' => ['nullable', Rule::in(['todo', 'in-progress', 'done'])],
    'due_date' => ['nullable', 'date'],
]);


        $task = $this->service->create($request->user()->id, $project, $data);

        return response()->json([
            'data' => $task,
        ], 201);
    }

    public function show(Request $request, int $task)
    {
        return response()->json([
            'data' => $this->service->get($request->user()->id, $task),
        ]);
    }

    public function update(Request $request, int $task)
    {
$data = $request->validate([
    'assigned_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],

    'title' => ['sometimes', 'required', 'string', 'max:255'],
    'description' => ['sometimes', 'nullable', 'string'],
    'status' => ['sometimes', 'nullable', Rule::in(['todo', 'in-progress', 'done'])],
    'due_date' => ['sometimes', 'nullable', 'date'],
]);


        return response()->json([
            'data' => $this->service->update($request->user()->id, $task, $data),
        ]);
    }

    public function destroy(Request $request, int $task)
    {
        $this->service->delete($request->user()->id, $task);

        return response()->json([
            'data' => ['success' => true],
        ]);
    }
}
