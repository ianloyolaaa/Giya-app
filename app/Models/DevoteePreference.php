<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevoteePreference extends Model
{
    protected $table = 'devotee_preferences';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'font_size', 'theme_style', 'language',
        'notify_mass_schedule', 'notify_itinerary',
        'notify_feast_day', 'notify_saved_destination',
        'created_at', 'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'notify_mass_schedule'     => 'boolean',
            'notify_itinerary'         => 'boolean',
            'notify_feast_day'         => 'boolean',
            'notify_saved_destination' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}