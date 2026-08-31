<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;

class TransactionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Transaction);
    }

    /**
     * Build a filtered query for transaction records.
     *
     * @param  array{
     *     search?: string,
     *     type?: string,
     *     category?: string,
     *     status?: string,
     *     month?: string,
     *     year?: string,
     * }  $filters
     */
    public function filter(array $filters): Builder
    {
        $query = $this->query();

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['month']) && ! empty($filters['year'])) {
            $query->whereMonth('date', $filters['month'])
                ->whereYear('date', $filters['year']);
        } elseif (! empty($filters['year'])) {
            $query->whereYear('date', $filters['year']);
        }

        if (! empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters) {
                $search = $filters['search'];
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('vendor', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('date', 'desc')->orderBy('id', 'desc');
    }

    protected function sortableColumns(): array
    {
        return ['date', 'amount', 'created_at'];
    }
}
