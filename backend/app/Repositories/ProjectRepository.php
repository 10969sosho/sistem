<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;

class ProjectRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Project);
    }

    /**
     * Build a filtered & searchable query for projects.
     *
     * @param  array{search?: string, status?: string, customer_id?: int}  $filters
     */
    public function filter(array $filters): Builder
    {
        $query = $this->query()
            ->with(['customer:id,name,pic_name,whatsapp,email,status'])
            ->withCount('tasks')
            ->withCount('openTasks');

        $this->applySearch($query, $filters['search'] ?? null);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        $sortBy = $filters['sort_by'] ?? 'deadline';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        return $this->applySort($query, $sortBy, $sortDir);
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if (! $search) {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            $q->where('projects.name', 'like', "%{$search}%")
                ->orWhere('projects.type', 'like', "%{$search}%")
                ->orWhere('projects.description', 'like', "%{$search}%")
                ->orWhereHas('customer', fn (Builder $c) => $c->where('name', 'like', "%{$search}%"));
        });
    }

    protected function sortableColumns(): array
    {
        return ['name', 'status', 'deadline', 'start_date', 'created_at'];
    }
}
