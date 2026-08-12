<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    public const STATUSES = ['new', 'contacted', 'interested', 'discussion', 'offer_sent', 'negotiation', 'deal', 'lost'];

    public const STATUS_LABELS = [
        'new' => 'New', 'contacted' => 'Contacted', 'interested' => 'Interested',
        'discussion' => 'Discussion', 'offer_sent' => 'Offer Sent', 'negotiation' => 'Negotiation',
        'deal' => 'Deal', 'lost' => 'Lost',
    ];

    public const SOURCES = [
        'meta_ads' => 'Meta Ads', 'whatsapp' => 'WhatsApp', 'instagram' => 'Instagram',
        'referral' => 'Referral', 'website' => 'Website', 'other' => 'Other',
    ];

    protected $fillable = [
        'name', 'company', 'email', 'phone', 'source', 'notes', 'requirement', 'entered_at',
        'status', 'estimated_value', 'deadline', 'deal_date', 'lost_reason', 'replied_at',
        'user_id', 'customer_id',
    ];

    protected function casts(): array
    {
        return ['entered_at' => 'date', 'deadline' => 'date', 'deal_date' => 'date', 'estimated_value' => 'decimal:2', 'replied_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function activities(): HasMany { return $this->hasMany(Activity::class); }
    public function opportunities(): HasMany { return $this->hasMany(Opportunity::class); }
    public function tasks(): HasMany { return $this->hasMany(Task::class); }
}
