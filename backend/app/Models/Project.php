<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['customer_id', 'name', 'type', 'description', 'status', 'deadline', 'pic', 'start_date', 'finish_date', 'internal_notes'])]
#[Hidden(['deleted_at'])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'start_date' => 'date',
            'finish_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function openTasks(): HasMany
    {
        return $this->hasMany(Task::class)->where('status', '!=', 'done');
    }

    public function hosting(): HasOne
    {
        return $this->hasOne(Hosting::class);
    }

    public function finance(): HasOne
    {
        return $this->hasOne(Finance::class);
    }
}
