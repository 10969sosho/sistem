<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    public function __construct(private TaskRepository $repository)
    {
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository
            ->filter($filters)
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();
    }

    public function show(int $id): Task
    {
        return $this->repository->findOrFail($id)
            ->load(['customer:id,name,pic_name,whatsapp,email,status', 'project:id,name,status,customer_id', 'project.customer:id,name', 'lead:id,name,status']);
    }

    public function create(array $data): Task
    {
        // Saat task dibuat dari project, customer otomatis mengikuti project.
        if (! empty($data['project_id']) && empty($data['customer_id'])) {
            $project = Project::withTrashed()->find($data['project_id']);
            $data['customer_id'] = $project?->customer_id;
        }

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Task
    {
        $task = $this->repository->findOrFail($id);

        if (! empty($data['project_id']) && $task->project_id != $data['project_id']) {
            $project = Project::withTrashed()->find($data['project_id']);
            $data['customer_id'] = $project?->customer_id;
        }

        return $this->repository->update($task, $data);
    }

    public function changeStatus(int $id, string $status): Task
    {
        return $this->repository->update($this->repository->findOrFail($id), [
            'status' => $status,
        ]);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($this->repository->findOrFail($id));
    }
}
