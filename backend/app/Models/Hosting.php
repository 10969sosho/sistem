<?php

namespace App\Models;

use Database\Factories\HostingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['project_id', 'provider', 'package', 'expired_date', 'domain', 'registrar', 'domain_expired_date', 'ssl_status', 'ssl_expired_date', 'server_ip', 'panel', 'username', 'notes'])]
#[Hidden(['deleted_at'])]
class Hosting extends Model
{
    /** @use HasFactory<HostingFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'expired_date' => 'date',
            'domain_expired_date' => 'date',
            'ssl_expired_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
