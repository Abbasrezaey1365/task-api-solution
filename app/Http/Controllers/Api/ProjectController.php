<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $service)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min($perPage, 100));

        return response()->json([
            'data' => $this->service->list($request->user()->id, $perPage),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $project = $this->service->create($request->user()->id, $data);

        return response()->json([
            'data' => $project,
        ], 201);
    }

    public function show(Request $request, int $project)
    {
        return response()->json([
            'data' => $this->service->get($request->user()->id, $project),
        ]);
    }

    public function update(Request $request, int $project)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        return response()->json([
            'data' => $this->service->update($request->user()->id, $project, $data),
        ]);
    }

    public function destroy(Request $request, int $project)
    {
        $this->service->delete($request->user()->id, $project);

        return response()->json([
            'data' => ['success' => true],
        ]);
    }
}
