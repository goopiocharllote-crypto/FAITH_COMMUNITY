<?php

namespace App\Payments;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A parish record that can be paid for with the parish GCash QR code (donation, Mass intention offering).
 */
interface Payable
{
    public function payments(): MorphMany;

    public function payer(): User;

    public function paymentAmount(): float;

    /** Short human label shown on the payment page, e.g. "Mass Intention offering". */
    public function paymentDescription(): string;

    /** Parish reference number, e.g. MI-2026-000012. */
    public function paymentReference(): string;

    /** True when the parishioner chose to pay through GCash. */
    public function isOnline(): bool;

    public function isPaid(): bool;

    /** True when the parishioner sent a GCash reference number that the parish has not confirmed yet. */
    public function isPaymentSubmitted(): bool;

    /** Record that a GCash reference number was submitted and is waiting for the parish office. */
    public function markPaymentSubmitted(): void;

    /** Record a confirmed payment on this record. */
    public function markAsPaid(): void;

    /** The page with the GCash QR code for this record. */
    public function paymentPageUrl(): string;

    /** Where the parishioner should land after submitting their reference number. */
    public function paymentReturnRoute(): string;
}
