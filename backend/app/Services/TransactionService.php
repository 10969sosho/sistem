<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionService
{
    public function __construct(private TransactionRepository $repository)
    {
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository
            ->filter($filters)
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();
    }

    public function show(int $id): Transaction
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): Transaction
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Transaction
    {
        $transaction = $this->repository->findOrFail($id);

        return $this->repository->update($transaction, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($this->repository->findOrFail($id));
    }

    /**
     * Get summary for a given month/year.
     */
    public function summary(int $year, ?int $month = null): array
    {
        $query = Transaction::query()->whereYear('date', $year);

        if ($month) {
            $query->whereMonth('date', $month);
        }

        $transactions = $query->get();

        $pemasukan = $transactions->where('type', 'pemasukan')->where('status', '!=', 'cancelled');
        $pengeluaran = $transactions->where('type', 'pengeluaran')->where('status', '!=', 'cancelled');

        $totalPemasukan = $pemasukan->sum('amount');
        $totalPengeluaran = $pengeluaran->sum('amount');

        // Category breakdown
        $pemasukanByCategory = $pemasukan->groupBy('category')
            ->map(fn ($items) => $items->sum('amount'))
            ->toArray();

        $pengeluaranByCategory = $pengeluaran->groupBy('category')
            ->map(fn ($items) => $items->sum('amount'))
            ->toArray();

        return [
            'year' => $year,
            'month' => $month,
            'total_pemasukan' => (float) $totalPemasukan,
            'total_pengeluaran' => (float) $totalPengeluaran,
            'laba_rugi' => (float) ($totalPemasukan - $totalPengeluaran),
            'pemasukan_by_category' => $pemasukanByCategory,
            'pengeluaran_by_category' => $pengeluaranByCategory,
            'kas_masuk' => (float) $pemasukan->where('status', 'paid')->sum('amount'),
            'kas_keluar' => (float) $pengeluaran->where('status', 'paid')->sum('amount'),
            'saldo_kas' => (float) $pemasukan->where('status', 'paid')->sum('amount') - $pengeluaran->where('status', 'paid')->sum('amount'),
        ];
    }
}
