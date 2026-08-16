<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** ERD entity: ItineraryType */
class ItineraryType extends Model
{
    protected $table = 'itinerary_types';

    public $timestamps = false;

    protected $fillable = ['name', 'description', 'is_active', 'created_at', 'updated_at'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(Itinerary::class);
    }
}
