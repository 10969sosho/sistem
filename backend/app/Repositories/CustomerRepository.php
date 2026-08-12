<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;

class CustomerRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Customer);
    }

    /**
     * Build a filtered & searchable query for customers.
     *
     * @param  array{search?: string, status?: string}  $filters
     */
    public function filter(array $filters): Builder
    {
        $query = $this->query()->withCount('projects');

        $this->applySearch($query, $filters['search'] ?? null);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        return $this->applySort($query, $sortBy, $sortDir);
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if (! $search) {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('company', 'like', "%{$search}%")
                ->orWhere('pic_name', 'like', "%{$search}%")
                ->orWhere('whatsapp', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    protected function sortableColumns(): array
    {
        return ['name', 'pic_name', 'status', 'created_at'];
    }
}
