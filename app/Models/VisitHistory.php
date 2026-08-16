<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** ERD entity: VisitHistory */
class VisitHistory extends Model
{
    protected $table = 'visit_history';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'church_id', 'itinerary_id', 'visited_at',
        'completion_status', 'notes', 'created_at', 'updated_at',
    ];

    protected $appends = ['church_name'];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(Itinerary::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class, 'visit_history_id');
    }

    public function getChurchNameAttribute(): string
    {
        return $this->church?->name ?? 'Unknown church';
    }

    /** The rating the devotee left for this specific visit, if any. */
    public function getRatingAttribute(): ?int
    {
        return $this->feedback()->value('rating');
    }
}
