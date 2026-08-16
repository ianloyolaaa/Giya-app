<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** ERD entity: Schedules */
class Schedule extends Model
{
    protected $table = 'schedules';

    public $timestamps = false;

    protected $fillable = [
        'church_id', 'event_name', 'event_type', 'schedule_date',
        'start_time', 'end_time', 'is_recurring', 'recurrence', 'notes', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
            'is_recurring'  => 'boolean',
            'created_at'    => 'datetime',
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q
            ->whereNull('schedule_date')
            ->orWhere('schedule_date', '>=', now()->toDateString()))
            ->orderBy('schedule_date')
            ->orderBy('start_time');
    }
}
