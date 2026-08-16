<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** ERD entity: Feedback */
class Feedback extends Model
{
    protected $table = 'feedback';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'church_id', 'visit_history_id', 'rating', 'comment', 'status', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'rating'     => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitHistory::class, 'visit_history_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'Approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'Pending');
    }
}
