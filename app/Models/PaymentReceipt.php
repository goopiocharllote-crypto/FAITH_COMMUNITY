<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The GCash receipt picture a parishioner uploaded for a payment.
 */
class PaymentReceipt extends Model
{
    /** Picture types accepted for a receipt. */
    public const MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    protected $fillable = [
        'payment_id',
        'mime',
        'original_name',
        'size',
        'data',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** The raw picture bytes. */
    public function contents(): string
    {
        return (string) base64_decode($this->data, true);
    }
}
