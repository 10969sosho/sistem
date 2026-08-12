<?php

namespace App\Repositories;

use App\Models\Finance;
use Illuminate\Database\Eloquent\Builder;

class FinanceRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Finance);
    }

    /**
     * Build a filtered query for finance records.
     *
     * @param  array{
     *     search?: string,
     *     project_id?: int,
     *     payment_status?: string,
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

        if (! empty($filters['payment_status'])) {
            $query = $this->applyPaymentStatus($query, $filters['payment_status']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        return $this->applySort($query, $sortBy, $sortDir);
    }

    private function applyPaymentStatus(Builder $query, string $status): Builder
    {
        return match ($status) {
            'belum_bayar' => $query->whereRaw('(dp + termin1 + termin2 + termin3 + pelunasan) <= 0'),
            'lunas' => $query->whereRaw('(dp + termin1 + termin2 + termin3 + pelunasan) >= total'),
            'sebagian' => $query->whereRaw('(dp + termin1 + termin2 + termin3 + pelunasan) > 0')
                ->whereRaw('(dp + termin1 + termin2 + termin3 + pelunasan) < total'),
            default => $query,
        };
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if (! $search) {
            return;
        }

        $query->whereHas('project', fn (Builder $p) => $p->where('name', 'like', "%{$search}%"));
    }

    protected function sortableColumns(): array
    {
        return ['total', 'created_at'];
    }
}
