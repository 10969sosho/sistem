<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    public const TYPES = ['whatsapp', 'call', 'meeting', 'note', 'lead_created', 'lead_updated', 'offer_sent', 'status_changed'];

    protected $fillable = ['user_id', 'lead_id', 'type', 'description'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function lead(): BelongsTo { return $this->belongsTo(Lead::class); }
}
