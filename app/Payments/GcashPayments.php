<?php

namespace App\Payments;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Manual GCash QR payments: the parishioner pays by scanning the parish QR code and then
 * submits the GCash reference number; the parish office confirms it.
 */
class GcashPayments
{
    public const GATEWAY = 'gcash_qr';

    /** GCash reference numbers are 13 digits; people often paste them with spaces. */
    public static function normalizeReference(string $reference): string
    {
        return preg_replace('/\D+/', '', $reference) ?? '';
    }

    /** True when the same reference number is already attached to a different record. */
    public function referenceTakenByAnother(string $reference, Model&Payable $payable): bool
    {
        return Payment::query()
            ->where('gateway_payment_id', $reference)
            ->whereIn('status', [Payment::STATUS_SUBMITTED, Payment::STATUS_PAID])
            ->where(fn ($query) => $query
                ->where('payable_type', '!=', $payable->getMorphClass())
                ->orWhere('payable_id', '!=', $payable->getKey()))
            ->exists();
    }

    /**
     * Save the reference number the parishioner entered. A newer reference replaces an earlier one.
     */
    public function submitReference(Model&Payable $payable, string $reference): Payment
    {
        return DB::transaction(function () use ($payable, $reference): Payment {
            $payable->payments()
                ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_SUBMITTED])
                ->update(['status' => Payment::STATUS_CANCELLED]);

            /** @var Payment $payment */
            $payment = $payable->payments()->create([
                'user_id' => $payable->payer()->id,
                'amount' => $payable->paymentAmount(),
                'method' => 'GCash',
                'gateway' => self::GATEWAY,
                'status' => Payment::STATUS_SUBMITTED,
                'gateway_payment_id' => $reference,
            ]);

            $payable->markPaymentSubmitted();

            return $payment;
        });
    }

    /**
     * The parish office confirmed the money arrived: mark the submitted GCash payment as paid.
     */
    public function recordConfirmed(Model&Payable $payable): void
    {
        $payable->payments()
            ->where('status', Payment::STATUS_SUBMITTED)
            ->update(['status' => Payment::STATUS_PAID, 'paid_at' => now()]);
    }

    /** The reference number the parishioner submitted for this record, if any. */
    public function submittedReference(Model&Payable $payable): ?string
    {
        return $payable->payments()
            ->whereIn('status', [Payment::STATUS_SUBMITTED, Payment::STATUS_PAID])
            ->latest('id')
            ->value('gateway_payment_id');
    }

    /**
     * URL of the parish GCash QR picture, or null when the image has not been added yet.
     * The picture may be saved as png, jpg, jpeg or webp under the configured name.
     */
    public static function qrImageUrl(): ?string
    {
        $path = config('payments.gcash.qr_image');

        if (! $path) {
            return null;
        }

        $base = preg_replace('/\.[A-Za-z0-9]+$/', '', $path);

        foreach ([$path, $base.'.png', $base.'.jpg', $base.'.jpeg', $base.'.webp'] as $candidate) {
            if (is_file(public_path($candidate))) {
                return asset($candidate);
            }
        }

        return null;
    }
}
