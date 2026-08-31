<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'search',
            'type',
            'category',
            'status',
            'month',
            'year',
            'per_page',
        ]);

        return TransactionResource::collection($this->service->paginate($filters));
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Transaksi berhasil ditambahkan.',
            'data' => new TransactionResource($transaction),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $transaction = $this->service->show($id);

        return response()->json([
            'data' => new TransactionResource($transaction),
        ]);
    }

    public function update(StoreTransactionRequest $request, int $id): JsonResponse
    {
        $transaction = $this->service->update($id, $request->validated());

        return response()->json([
            'message' => 'Transaksi berhasil diperbarui.',
            'data' => new TransactionResource($transaction),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'message' => 'Transaksi berhasil dihapus.',
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $year = (int) $request->query('year', now()->year);
        $month = $request->query('month') ? (int) $request->query('month') : null;

        return response()->json([
            'data' => $this->service->summary($year, $month),
        ]);
    }
}
