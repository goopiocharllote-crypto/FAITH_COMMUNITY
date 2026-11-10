<?php

namespace App\Payments;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A parish record that can be paid for online (donation, Mass intention offering).
 */
interface Payable
{
    public function payments(): MorphMany;

    public function payer(): User;

    public function paymentAmount(): float;

    /** Short human label shown on the checkout page, e.g. "Mass Intention offering". */
    public function paymentDescription(): string;

    /** Parish reference number, e.g. MI-2026-000012. */
    public function paymentReference(): string;

    /** True when the parishioner chose to pay online through GCash. */
    public function isOnline(): bool;

    public function isPaid(): bool;

    /** Record a confirmed online payment on this record. */
    public function markAsPaid(): void;

    /** Where the parishioner should land after checkout. */
    public function paymentReturnRoute(): string;
}
