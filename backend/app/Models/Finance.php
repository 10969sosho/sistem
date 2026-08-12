<?php

namespace App\Models;

use Database\Factories\FinanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['project_id', 'total', 'dp', 'termin1', 'termin2', 'termin3', 'pelunasan'])]
#[Hidden(['deleted_at'])]
class Finance extends Model
{
    /** @use HasFactory<FinanceFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'dp' => 'decimal:2',
            'termin1' => 'decimal:2',
            'termin2' => 'decimal:2',
            'termin3' => 'decimal:2',
            'pelunasan' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function totalPaid(): Attribute
    {
        return Attribute::get(fn () => round(
            (float) $this->dp + (float) $this->termin1 + (float) $this->termin2 + (float) $this->termin3 + (float) $this->pelunasan,
            2,
        ));
    }

    public function remaining(): Attribute
    {
        return Attribute::get(fn () => round(max(0, (float) $this->total - $this->total_paid), 2));
    }

    /**
     * Status pembayaran: belum_bayar | sebagian | lunas
     */
    public function paymentStatus(): Attribute
    {
        return Attribute::get(function () {
            $total = (float) $this->total;
            $paid = (float) $this->total_paid;

            if ($paid <= 0) {
                return 'belum_bayar';
            }

            return $paid >= $total ? 'lunas' : 'sebagian';
        });
    }
}
