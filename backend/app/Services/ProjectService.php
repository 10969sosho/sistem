<?php

namespace App\Services;

use App\Models\Project;
use App\Repositories\ProjectRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function __construct(private ProjectRepository $repository)
    {
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository
            ->filter($filters)
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();
    }

    public function show(int $id): Project
    {
        $project = $this->repository->findOrFail($id);
        $project->load([
            'customer',
            'tasks' => fn ($query) => $query->latest('created_at'),
            'hosting',
            'finance',
        ]);

        return $project;
    }

    public function create(array $data): Project
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Project
    {
        return $this->repository->update($this->repository->findOrFail($id), $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($this->repository->findOrFail($id));
    }

    /**
     * Lightweight options for dropdowns, optionally scoped to a customer.
     */
    public function options(?int $customerId = null, ?string $search = null): Collection
    {
        $query = Project::query()
            ->select('id', 'name', 'status', 'customer_id')
            ->orderBy('name');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->limit(50)->get();
    }
}
