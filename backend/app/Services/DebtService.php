<?php

namespace App\Services;

use App\Models\Debt;
use App\Repositories\DebtRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class DebtService
{
    public function __construct(private DebtRepository $repository)
    {
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository
            ->filter($filters)
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();
    }

    public function show(int $id): Debt
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): Debt
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Debt
    {
        $debt = $this->repository->findOrFail($id);

        return $this->repository->update($debt, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($this->repository->findOrFail($id));
    }

    /**
     * Get debt summary by person.
     */
    public function summary(): array
    {
        $debts = Debt::query()->get();

        $totalBelumDibayar = $debts->where('status', 'belum_dibayar')->sum('amount');
        $totalDibayarSebagian = $debts->where('status', 'dibayar_sebagian')->sum('amount');
        $totalLunas = $debts->where('status', 'lunas')->sum('amount');

        $byPerson = $debts->groupBy('person')->map(function ($items, $person) {
            return [
                'total' => (float) $items->sum('amount'),
                'belum_dibayar' => (float) $items->where('status', 'belum_dibayar')->sum('amount'),
                'lunas' => (float) $items->where('status', 'lunas')->sum('amount'),
                'count' => $items->count(),
            ];
        })->toArray();

        return [
            'total_all' => (float) $debts->sum('amount'),
            'total_belum_dibayar' => (float) $totalBelumDibayar,
            'total_dibayar_sebagian' => (float) $totalDibayarSebagian,
            'total_lunas' => (float) $totalLunas,
            'by_person' => $byPerson,
        ];
    }
}
