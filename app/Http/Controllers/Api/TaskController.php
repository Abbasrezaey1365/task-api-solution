<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $service
    ) {}


    public function index(Request $request, ?int $project = null): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $projectId = $project ?? (int) $request->query('project_id', 0);
        if ($projectId <= 0) {
            return response()->json(['message' => 'project_id is required'], 422);
        }

        $filters = array_filter([
            'status' => $request->query('status'),
        ], fn ($v) => $v !== null && $v !== '');

        $perPage = (int) $request->query('per_page', 50);

        $page = $this->service->list(
            userId: $userId,
            projectId: $projectId,
            filters: $filters,
            perPage: $perPage
        );

        return response()->json($page);
    }

    public function store(Request $request, ?int $project = null): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $data = $request->validate([
            'project_id' => ['sometimes', 'integer'],

            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],

            'status' => ['sometimes', Rule::in(['todo', 'doing', 'done'])],
            'due_date' => ['nullable', 'date'],

            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],

            'assigned_user_id' => ['nullable', 'integer'],
        ]);

        $projectId = $project ?? (int) ($data['project_id'] ?? 0);
        if ($projectId <= 0) {
            return response()->json(['message' => 'project_id is required'], 422);
        }

        $task = $this->service->create(
            userId: $userId,
            projectId: $projectId,
            data: $data
        );

        return response()->json($task, 201);
    }

    public function show(Request $request, int $task): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $model = $this->service->get($userId, $task);

        return response()->json($model);
    }

    public function update(Request $request, int $task): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $data = $request->validate([
            'title' => ['sometimes', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', Rule::in(['todo', 'doing', 'done'])],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'assignee_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'assigned_user_id' => ['sometimes', 'nullable', 'integer'],
        ]);

        $updated = $this->service->update($userId, $task, $data);

        return response()->json($updated, 200);
    }

    public function destroy(Request $request, int $task): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $this->service->delete($userId, $task);

        return response()->json(['deleted' => true], 200);
    }
}
