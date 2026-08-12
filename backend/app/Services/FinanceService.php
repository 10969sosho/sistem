<?php

namespace App\Services;

use App\Models\Finance;
use App\Models\Project;
use App\Repositories\FinanceRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class FinanceService
{
    public function __construct(private FinanceRepository $repository)
    {
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository
            ->filter($filters)
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();
    }

    public function show(int $id): Finance
    {
        return $this->repository->findOrFail($id)
            ->load(['project:id,name,status,customer_id', 'project.customer:id,name']);
    }

    /**
     * Ambil data finance milik sebuah project (bisa null).
     */
    public function forProject(int $projectId): ?Finance
    {
        $this->ensureProjectExists($projectId);

        return Finance::query()
            ->where('project_id', $projectId)
            ->first();
    }

    /**
     * Buat atau perbarui data finance milik sebuah project.
     */
    public function upsert(int $projectId, array $data): Finance
    {
        $this->ensureProjectExists($projectId);

        return Finance::query()->updateOrCreate(
            ['project_id' => $projectId],
            collect($data)->except('project_id')->all(),
        );
    }

    public function delete(int $id): void
    {
        $this->repository->delete($this->repository->findOrFail($id));
    }

    private function ensureProjectExists(int $projectId): void
    {
        Project::findOrFail($projectId);
    }
}
