<?php

namespace App\Models;

use App\Payments\Payable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MassIntention extends Model implements Payable
{
    use HasFactory;

    public const METHOD_ONLINE = 'GCash';

    public const METHOD_PARISH = 'Cash';

    /** Minutes a request may wait for the parish to act on it before it is rejected automatically. */
    public const PENDING_EXPIRY_MINUTES = 10;

    /**
     * Rejects every request still Pending after the expiry window, which frees the Mass
     * schedule for the parishioner to book again. Requests whose GCash receipt was already
     * submitted (or paid) are left for the parish to review.
     */
    public static function rejectExpired(): int
    {
        return static::query()
            ->where('status', 'Pending')
            ->whereNotIn('payment_status', ['Verifying', 'Paid'])
            ->where('created_at', '<=', now()->subMinutes(max(1, (int) ParishSetting::get('pending_expiry_minutes') ?: self::PENDING_EXPIRY_MINUTES)))
            ->update(['status' => 'Rejected']);
    }

    protected $fillable = [
        'schedule_id',
        'intention_type',
        'intention_for',
        'additional_message',
        'mass_date',
        'mobile',
        'offering_amount',
        'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'mass_date' => 'datetime',
            'offering_amount' => 'decimal:2',
            'church_read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MassSchedule::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function referenceNumber(): string
    {
        return 'MI-'.$this->created_at->format('Y').'-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
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
        return (float) $this->offering_amount;
    }

    public function paymentDescription(): string
    {
        return 'Mass Intention offering ('.$this->intention_type.')';
    }

    public function paymentReference(): string
    {
        return $this->referenceNumber();
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'Paid';
    }

    public function markAsPaid(): void
    {
        $this->forceFill(['payment_status' => 'Paid'])->save();
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
        return route('payments.mass-intentions.pay', $this);
    }

    public function paymentReturnRoute(): string
    {
        return route('mass-intentions.confirmation', $this);
    }
}
