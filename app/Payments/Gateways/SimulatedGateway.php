<?php

namespace App\Payments\Gateways;

use App\Models\Payment;
use App\Payments\Payable;
use App\Payments\PaymentGateway;
use Illuminate\Support\Str;

/**
 * Local stand-in for a GCash checkout, used until PayMongo keys are configured.
 * The parishioner is sent to an in-app page where they can approve or cancel.
 */
class SimulatedGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'simulator';
    }

    public function createCheckout(Payment $payment, Payable $payable, string $successUrl, string $cancelUrl): array
    {
        return [
            'id' => 'sim_cs_'.Str::lower(Str::random(24)),
            'url' => route('payments.simulator.show', $payment),
        ];
    }

    public function fetchStatus(Payment $payment): array
    {
        // The simulator page updates the payment directly, so the stored state is the truth.
        return ['status' => $payment->status, 'payment_id' => $payment->gateway_payment_id];
    }
}
