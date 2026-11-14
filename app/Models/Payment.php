<?php

namespace App\Models;

use App\Payments\Payable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    public const STATUS_PENDING = 'pending';

    /** The parishioner sent a GCash reference number; the parish office has not confirmed it yet. */
    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'amount',
        'method',
        'gateway',
        'status',
        'checkout_id',
        'checkout_url',
        'gateway_payment_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return MorphTo<Model&Payable, $this> */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
