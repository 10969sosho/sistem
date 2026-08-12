<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHostingRequest;
use App\Http\Resources\HostingResource;
use App\Services\HostingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HostingController extends Controller
{
    public function __construct(private HostingService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'search',
            'project_id',
            'expiring_days',
            'sort_by',
            'sort_dir',
            'per_page',
        ]);

        return HostingResource::collection($this->service->paginate($filters));
    }

    public function store(StoreHostingRequest $request): JsonResponse
    {
        $projectId = (int) $request->validated('project_id');
        $hosting = $this->service->upsert($projectId, $request->validated());

        return response()->json([
            'message' => 'Data hosting berhasil disimpan.',
            'data' => new HostingResource($hosting->load('project.customer')),
        ]);
    }

    public function show(int $projectId): JsonResponse
    {
        $hosting = $this->service->forProject($projectId);

        if ($hosting === null) {
            return response()->json([
                'message' => 'Data hosting belum ada.',
                'data' => null,
            ]);
        }

        return response()->json([
            'data' => new HostingResource($hosting->load('project.customer')),
        ]);
    }

    public function record(int $id): JsonResponse
    {
        return response()->json([
            'data' => new HostingResource($this->service->show($id)),
        ]);
    }

    public function destroy(int $projectId): JsonResponse
    {
        $hosting = $this->service->forProject($projectId);
        if ($hosting) {
            $this->service->delete($hosting->id);
        }

        return response()->json([
            'message' => 'Data hosting berhasil dihapus.',
        ]);
    }
}
