<?php

namespace App\Services;

use App\Models\Hosting;
use App\Models\Project;
use App\Repositories\HostingRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class HostingService
{
    public function __construct(private HostingRepository $repository)
    {
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository
            ->filter($filters)
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();
    }

    public function show(int $id): Hosting
    {
        return $this->repository->findOrFail($id)
            ->load(['project:id,name,status,customer_id', 'project.customer:id,name']);
    }

    /**
     * Ambil data hosting milik sebuah project (bisa null).
     */
    public function forProject(int $projectId): ?Hosting
    {
        $this->ensureProjectExists($projectId);

        return Hosting::query()
            ->where('project_id', $projectId)
            ->first();
    }

    /**
     * Buat atau perbarui data hosting milik sebuah project.
     */
    public function upsert(int $projectId, array $data): Hosting
    {
        $this->ensureProjectExists($projectId);

        return Hosting::query()->updateOrCreate(
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
