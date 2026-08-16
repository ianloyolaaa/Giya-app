<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ERD entity: Transactions
 *
 * $transaction->plan           -> the SubscriptionPlans NAME (relation is subscriptionPlan)
 * $transaction->transaction_id -> formatted from the primary key
 */
class Transaction extends Model
{
    protected $table = 'transactions';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'plan_type_id', 'amount', 'currency', 'method',
        'status', 'reference_no', 'notes', 'created_at', 'processed_at', 'updated_at',
    ];

    protected $appends = ['transaction_id', 'plan'];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'created_at'   => 'datetime',
            'processed_at' => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_type_id');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'Paid');
    }

    /** Human-facing reference, e.g. TXN-000042. */
    public function getTransactionIdAttribute(): string
    {
        return 'TXN-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    /** Old views printed $transaction->plan as a string. */
    public function getPlanAttribute(): string
    {
        return $this->subscriptionPlan?->name ?? 'Unknown plan';
    }

    public function amountLabel(): string
    {
        return $this->currency.' '.number_format((float) $this->amount, 2);
    }

    /** Has this paid transaction's coverage window expired yet? */
    public function isStillValid(): bool
    {
        if ($this->status !== 'Paid' || ! $this->processed_at) {
            return false;
        }

        $days = $this->subscriptionPlan?->duration_days ?? 0;

        return $this->processed_at->copy()->addDays($days)->isFuture();
    }
}
