<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ERD entity: ItineraryStops
 *
 * $stop->church_name now reads through to the Churches row instead of being a
 * duplicated string column.
 */
class ItineraryStop extends Model
{
    protected $table = 'itinerary_stops';

    public $timestamps = false;

    protected $fillable = [
        'itinerary_id', 'church_id', 'stop_order', 'is_visited', 'visited_at', 'notes',
    ];

    protected $appends = ['church_name'];

    protected function casts(): array
    {
        return [
            'is_visited' => 'boolean',
            'visited_at' => 'datetime',
        ];
    }

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(Itinerary::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function getChurchNameAttribute(): string
    {
        return $this->church?->name ?? 'Unknown church';
    }
}
