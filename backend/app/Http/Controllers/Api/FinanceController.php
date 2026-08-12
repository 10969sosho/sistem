<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFinanceRequest;
use App\Http\Resources\FinanceResource;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FinanceController extends Controller
{
    public function __construct(private FinanceService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'search',
            'project_id',
            'payment_status',
            'sort_by',
            'sort_dir',
            'per_page',
        ]);

        return FinanceResource::collection($this->service->paginate($filters));
    }

    public function store(StoreFinanceRequest $request): JsonResponse
    {
        $projectId = (int) $request->validated('project_id');
        $finance = $this->service->upsert($projectId, $request->validated());

        return response()->json([
            'message' => 'Data keuangan berhasil disimpan.',
            'data' => new FinanceResource($finance->load('project.customer')),
        ]);
    }

    public function show(int $projectId): JsonResponse
    {
        $finance = $this->service->forProject($projectId);

        if ($finance === null) {
            return response()->json([
                'message' => 'Data keuangan belum ada.',
                'data' => null,
            ]);
        }

        return response()->json([
            'data' => new FinanceResource($finance->load('project.customer')),
        ]);
    }

    public function record(int $id): JsonResponse
    {
        return response()->json([
            'data' => new FinanceResource($this->service->show($id)),
        ]);
    }

    public function destroy(int $projectId): JsonResponse
    {
        $finance = $this->service->forProject($projectId);
        if ($finance) {
            $this->service->delete($finance->id);
        }

        return response()->json([
            'message' => 'Data keuangan berhasil dihapus.',
        ]);
    }
}
