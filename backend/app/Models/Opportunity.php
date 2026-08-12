<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use SoftDeletes;

    public const STAGES = Lead::STATUSES;

    protected $fillable = [
        'title', 'lead_id', 'customer_id', 'value', 'stage', 'probability', 'proposal_sent_at',
        'offer_date', 'deal_date', 'expected_close_date', 'notes', 'user_id',
    ];

    protected function casts(): array
    {
        return ['value' => 'decimal:2', 'probability' => 'integer', 'proposal_sent_at' => 'datetime', 'offer_date' => 'date', 'deal_date' => 'date', 'expected_close_date' => 'date'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function lead(): BelongsTo { return $this->belongsTo(Lead::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}
