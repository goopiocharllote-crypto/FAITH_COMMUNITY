<?php

namespace App\Models;

use App\Payments\Payable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Donation extends Model implements Payable
{
    use HasFactory;

    public const METHOD_ONLINE = 'GCash';

    public const METHOD_PARISH = 'Cash';

    protected $fillable = [
        'user_id',
        'amount',
        'payment_method',
        'payment_status',
        'reference_number',
        'message',
        'status',
    ];

    protected $attributes = [
        'payment_status' => 'Pending',
        'status' => 'Pending',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function isOnline(): bool
    {
        return $this->payment_method === self::METHOD_ONLINE;
    }

    public function payer(): User
    {
        return $this->user;
    }

    public function paymentAmount(): float
    {
        return (float) $this->amount;
    }

    public function paymentDescription(): string
    {
        return 'Donation to Faith Community Loboc Parish';
    }

    public function paymentReference(): string
    {
        return 'DN-'.$this->created_at->format('Y').'-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'Paid';
    }

    public function markAsPaid(): void
    {
        // The parish office confirmed the money arrived.
        $this->forceFill([
            'payment_status' => 'Paid',
            'status' => 'Approved',
        ])->save();
    }

    public function isPaymentSubmitted(): bool
    {
        return $this->payment_status === 'Verifying';
    }

    public function markPaymentSubmitted(): void
    {
        $this->forceFill(['payment_status' => 'Verifying'])->save();
    }

    public function paymentPageUrl(): string
    {
        return route('payments.donations.pay', $this);
    }

    public function paymentReturnRoute(): string
    {
        return route('donations.index');
    }
}
