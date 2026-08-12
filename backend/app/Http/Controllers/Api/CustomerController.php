<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function __construct(private CustomerService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'status', 'sort_by', 'sort_dir', 'per_page']);

        return CustomerResource::collection($this->service->paginate($filters));
    }

    public function show(int $id): CustomerResource
    {
        return new CustomerResource($this->service->show($id));
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Customer berhasil ditambahkan.',
            'data' => new CustomerResource($customer),
        ], 201);
    }

    public function update(UpdateCustomerRequest $request, int $id): JsonResponse
    {
        $customer = $this->service->update($id, $request->validated());

        return response()->json([
            'message' => 'Customer berhasil diperbarui.',
            'data' => new CustomerResource($customer),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'message' => 'Customer berhasil dihapus.',
        ]);
    }
}
