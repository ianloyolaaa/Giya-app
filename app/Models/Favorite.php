<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** ERD entity: Favorites (UNIQUE user_id + church_id) */
class Favorite extends Model
{
    protected $table = 'favorites';

    public $timestamps = false;

    protected $fillable = ['user_id', 'church_id', 'is_active', 'created_at'];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
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

    /** Save or un-save a church without ever violating the unique index. */
    public static function toggle(int $userId, int $churchId): bool
    {
        $row = static::firstOrNew(['user_id' => $userId, 'church_id' => $churchId]);
        $row->is_active = $row->exists ? ! $row->is_active : true;

        if (! $row->exists) {
            $row->created_at = now();
        }

        $row->save();

        return $row->is_active;
    }
}
