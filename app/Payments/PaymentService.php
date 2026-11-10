<?php

namespace App\Payments;

use App\Models\Payment;
use App\Notifications\ParishUpdate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(private readonly PaymentGateway $gateway)
    {
    }

    public function gatewayName(): string
    {
        return $this->gateway->name();
    }

    /**
     * Start (or restart) a GCash checkout for a donation / Mass intention offering.
     *
     * @throws PaymentGatewayException
     */
    public function startCheckout(Model&Payable $payable): Payment
    {
        if ($payable->isPaid()) {
            throw new PaymentGatewayException('This record is already paid.');
        }

        // Any earlier, abandoned checkout for the same record is superseded.
        $payable->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->update(['status' => Payment::STATUS_CANCELLED]);

        /** @var Payment $payment */
        $payment = $payable->payments()->create([
            'user_id' => $payable->payer()->id,
            'amount' => $payable->paymentAmount(),
            'method' => 'GCash',
            'gateway' => $this->gateway->name(),
            'status' => Payment::STATUS_PENDING,
        ]);

        try {
            $checkout = $this->gateway->createCheckout(
                $payment,
                $payable,
                route('payments.return', ['payment' => $payment, 'result' => 'success']),
                route('payments.return', ['payment' => $payment, 'result' => 'cancel']),
            );
        } catch (PaymentGatewayException $exception) {
            $payment->update(['status' => Payment::STATUS_FAILED]);
            Log::error('GCash checkout could not be started', [
                'payment_id' => $payment->id,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $payment->update([
            'checkout_id' => $checkout['id'],
            'checkout_url' => $checkout['url'],
        ]);

        return $payment;
    }

    /**
     * Ask the gateway for the latest state and apply it.
     */
    public function refresh(Payment $payment): Payment
    {
        if (! $payment->isPending() || ! $payment->checkout_id) {
            return $payment;
        }

        try {
            $result = $this->gateway->fetchStatus($payment);
        } catch (PaymentGatewayException $exception) {
            Log::warning('Could not refresh GCash payment status', [
                'payment_id' => $payment->id,
                'error' => $exception->getMessage(),
            ]);

            return $payment;
        }

        return match ($result['status']) {
            Payment::STATUS_PAID => $this->markPaid($payment, $result['payment_id']),
            Payment::STATUS_CANCELLED, Payment::STATUS_FAILED => $this->markUnsuccessful($payment, $result['status']),
            default => $payment,
        };
    }

    /**
     * Record a confirmed payment. Safe to call more than once (return URL + webhook).
     */
    public function markPaid(Payment $payment, ?string $gatewayPaymentId = null): Payment
    {
        $justPaid = DB::transaction(function () use ($payment, $gatewayPaymentId): bool {
            /** @var Payment $locked */
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($locked->isPaid()) {
                return false;
            }

            $locked->forceFill([
                'status' => Payment::STATUS_PAID,
                'gateway_payment_id' => $gatewayPaymentId ?? $locked->gateway_payment_id,
                'paid_at' => now(),
            ])->save();

            $locked->payable->markAsPaid();

            return true;
        });

        $payment->refresh();

        if ($justPaid) {
            $payment->user->notify(ParishUpdate::paymentReceived($payment));
        }

        return $payment;
    }

    public function markUnsuccessful(Payment $payment, string $status = Payment::STATUS_CANCELLED): Payment
    {
        Payment::query()
            ->whereKey($payment->id)
            ->where('status', Payment::STATUS_PENDING)
            ->update(['status' => $status]);

        return $payment->refresh();
    }
}
