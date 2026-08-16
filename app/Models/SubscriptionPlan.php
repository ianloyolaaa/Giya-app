<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** ERD entity: SubscriptionPlans */
class SubscriptionPlan extends Model
{
    protected $table = 'subscription_plans';

    public $timestamps = false;

    protected $fillable = [
        'name', 'description', 'price', 'currency',
        'duration_days', 'is_active', 'created_at', 'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'price'         => 'decimal:2',
            'duration_days' => 'integer',
            'is_active'     => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'plan_type_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function priceLabel(): string
    {
        return $this->currency.' '.number_format((float) $this->price, 2);
    }
}
