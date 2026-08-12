<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function __construct(private CustomerRepository $repository)
    {
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository
            ->filter($filters)
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();
    }

    public function show(int $id): Customer
    {
        $customer = $this->repository->findOrFail($id);
        $customer->load([
            'projects' => fn ($query) => $query->withCount('openTasks')->latest('created_at'),
        ]);

        return $customer;
    }

    public function create(array $data): Customer
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Customer
    {
        return $this->repository->update($this->repository->findOrFail($id), $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($this->repository->findOrFail($id));
    }

    /**
     * Lightweight options for dropdowns.
     */
    public function options(?string $search = null): Collection
    {
        $query = Customer::query()
            ->select('id', 'name', 'status')
            ->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->limit(50)->get();
    }
}
