<?php

namespace App\Repositories;

use App\Models\Debt;
use Illuminate\Database\Eloquent\Builder;

class DebtRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Debt);
    }

    /**
     * Build a filtered query for debt records.
     *
     * @param  array{
     *     search?: string,
     *     type?: string,
     *     person?: string,
     *     status?: string,
     * }  $filters
     */
    public function filter(array $filters): Builder
    {
        $query = $this->query();

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['person'])) {
            $query->where('person', $filters['person']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters) {
                $search = $filters['search'];
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('person', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('date', 'desc')->orderBy('id', 'desc');
    }

    protected function sortableColumns(): array
    {
        return ['date', 'amount', 'created_at'];
    }
}
