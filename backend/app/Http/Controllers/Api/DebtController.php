<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDebtRequest;
use App\Http\Resources\DebtResource;
use App\Services\DebtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DebtController extends Controller
{
    public function __construct(private DebtService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'search',
            'type',
            'person',
            'status',
            'per_page',
        ]);

        return DebtResource::collection($this->service->paginate($filters));
    }

    public function store(StoreDebtRequest $request): JsonResponse
    {
        $debt = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Data hutang berhasil ditambahkan.',
            'data' => new DebtResource($debt),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $debt = $this->service->show($id);

        return response()->json([
            'data' => new DebtResource($debt),
        ]);
    }

    public function update(StoreDebtRequest $request, int $id): JsonResponse
    {
        $debt = $this->service->update($id, $request->validated());

        return response()->json([
            'message' => 'Data hutang berhasil diperbarui.',
            'data' => new DebtResource($debt),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'message' => 'Data hutang berhasil dihapus.',
        ]);
    }

    public function summary(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->summary(),
        ]);
    }
}
