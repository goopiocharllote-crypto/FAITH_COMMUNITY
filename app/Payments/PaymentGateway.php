<?php

namespace App\Payments;

use App\Models\Payment;

interface PaymentGateway
{
    /** Gateway key stored on each payment ("paymongo", "simulator"). */
    public function name(): string;

    /**
     * Open a hosted GCash checkout.
     *
     * @return array{id: string, url: string}
     *
     * @throws PaymentGatewayException
     */
    public function createCheckout(Payment $payment, Payable $payable, string $successUrl, string $cancelUrl): array;

    /**
     * Ask the gateway what happened to a checkout.
     *
     * @return array{status: string, payment_id: string|null} status is one of the Payment::STATUS_* values
     *
     * @throws PaymentGatewayException
     */
    public function fetchStatus(Payment $payment): array;
}
