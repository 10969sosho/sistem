<?php

namespace App\Repositories;

use App\Models\Hosting;
use Illuminate\Database\Eloquent\Builder;

class HostingRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Hosting);
    }

    /**
     * Build a filtered query for hosting records.
     *
     * @param  array{
     *     search?: string,
     *     project_id?: int,
     *     expiring_days?: int,
     * }  $filters
     */
    public function filter(array $filters): Builder
    {
        $query = $this->query()
            ->with(['project:id,name,status,customer_id', 'project.customer:id,name']);

        $this->applySearch($query, $filters['search'] ?? null);

        if (! empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (! empty($filters['expiring_days'])) {
            $end = now()->copy()->addDays((int) $filters['expiring_days'])->endOfDay();
            $query->where(function (Builder $q) use ($end) {
                $q->whereDate('expired_date', '<=', $end->toDateString())
                    ->orWhereDate('domain_expired_date', '<=', $end->toDateString());
            });
        }

        $sortBy = $filters['sort_by'] ?? 'expired_date';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        return $this->applySort($query, $sortBy, $sortDir);
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if (! $search) {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            $q->where('domain', 'like', "%{$search}%")
                ->orWhere('provider', 'like', "%{$search}%")
                ->orWhere('registrar', 'like', "%{$search}%")
                ->orWhereHas('project', fn (Builder $p) => $p->where('name', 'like', "%{$search}%"));
        });
    }

    protected function sortableColumns(): array
    {
        return ['provider', 'domain', 'expired_date', 'domain_expired_date', 'created_at'];
    }
}
