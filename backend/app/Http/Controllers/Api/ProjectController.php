<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'status', 'customer_id', 'sort_by', 'sort_dir', 'per_page']);

        return ProjectResource::collection($this->service->paginate($filters));
    }

    public function show(int $id): ProjectResource
    {
        return new ProjectResource($this->service->show($id));
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Project berhasil ditambahkan.',
            'data' => new ProjectResource($project->load('customer')),
        ], 201);
    }

    public function update(UpdateProjectRequest $request, int $id): JsonResponse
    {
        $project = $this->service->update($id, $request->validated());

        return response()->json([
            'message' => 'Project berhasil diperbarui.',
            'data' => new ProjectResource($project->load('customer')),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'message' => 'Project berhasil dihapus.',
        ]);
    }
}
