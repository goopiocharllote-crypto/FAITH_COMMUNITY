<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\MassIntention;
use App\Models\Payment;
use App\Payments\Gateways\PayMongoGateway;
use App\Payments\Payable;
use App\Payments\PaymentGatewayException;
use App\Payments\PaymentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    /** "Pay now" for an unpaid GCash donation. */
    public function payDonation(Request $request, Donation $donation): RedirectResponse
    {
        abort_unless($donation->user_id === $request->user()->id, 403);

        return $this->redirectToCheckout($donation);
    }

    /** "Pay now" for an unpaid GCash Mass intention offering. */
    public function payMassIntention(Request $request, MassIntention $massIntention): RedirectResponse
    {
        abort_unless($massIntention->user_id === $request->user()->id, 403);

        return $this->redirectToCheckout($massIntention);
    }

    /**
     * Send the parishioner to the GCash checkout for a record they just created or want to pay.
     */
    public function redirectToCheckout(Model&Payable $payable): RedirectResponse
    {
        $returnTo = redirect()->to($payable->paymentReturnRoute());

        if (! $payable->isOnline() || $payable->isPaid()) {
            return $returnTo->withErrors(['payment' => __('This record has no GCash payment due.')]);
        }

        if ($payable->status === 'Rejected') {
            return $returnTo->withErrors(['payment' => __('This request was not accepted, so no payment is needed.')]);
        }

        try {
            $payment = $this->payments->startCheckout($payable);
        } catch (PaymentGatewayException) {
            return $returnTo->withErrors([
                'payment' => __('We could not open the GCash checkout right now. Your request was saved — use "Pay with GCash" to try again later.'),
            ]);
        }

        return redirect()->away($payment->checkout_url);
    }

    /**
     * Where GCash/PayMongo sends the parishioner back after checkout.
     */
    public function return(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        $payment = $this->payments->refresh($payment);

        if (! $payment->isPaid() && $request->query('result') === 'cancel') {
            $payment = $this->payments->markUnsuccessful($payment);
        }

        $destination = redirect()->to($payment->payable->paymentReturnRoute());

        return match (true) {
            $payment->isPaid() => $destination->with('status', __('GCash payment of ₱:amount received. Thank you! A confirmation was sent to your email and mobile number.', [
                'amount' => number_format((float) $payment->amount, 2),
            ])),
            $payment->isPending() => $destination->with('status', __('Your GCash payment is still being confirmed. This page will show "Paid" once GCash confirms it.')),
            default => $destination->withErrors(['payment' => __('The GCash payment was cancelled. Your request is saved — you can pay again anytime.')]),
        };
    }

    // ------------------------------------------------------------------
    // Local GCash simulator (PAYMENT_GATEWAY=simulator)
    // ------------------------------------------------------------------

    public function showSimulator(Request $request, Payment $payment): View
    {
        $this->guardSimulator($request, $payment);

        return view()->file(resource_path('users/payment-simulator.blade.php'), [
            'payment' => $payment,
            'payable' => $payment->payable,
        ]);
    }

    public function completeSimulator(Request $request, Payment $payment): RedirectResponse
    {
        $this->guardSimulator($request, $payment);

        $validated = $request->validate(['decision' => ['required', 'in:pay,cancel']]);

        if ($payment->isPending()) {
            if ($validated['decision'] === 'pay') {
                $this->payments->markPaid($payment, 'sim_pay_'.Str::lower(Str::random(20)));
            } else {
                $this->payments->markUnsuccessful($payment);
            }
        }

        return redirect()->route('payments.return', [
            'payment' => $payment,
            'result' => $validated['decision'] === 'pay' ? 'success' : 'cancel',
        ]);
    }

    private function guardSimulator(Request $request, Payment $payment): void
    {
        abort_unless($payment->gateway === 'simulator' && config('payments.gateway') === 'simulator', 404);
        abort_unless($payment->user_id === $request->user()->id, 403);
    }

    // ------------------------------------------------------------------
    // PayMongo webhook (checkout_session.payment.paid)
    // ------------------------------------------------------------------

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();

        if (! PayMongoGateway::signatureIsValid(
            (string) $request->header('Paymongo-Signature', ''),
            $payload,
            config('payments.paymongo.webhook_secret'),
        )) {
            Log::warning('Rejected PayMongo webhook with an invalid signature.');

            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $event = json_decode($payload, true) ?? [];
        $type = $event['data']['attributes']['type'] ?? null;
        $session = $event['data']['attributes']['data'] ?? [];

        if ($type === 'checkout_session.payment.paid' && isset($session['id'])) {
            $payment = Payment::query()->where('checkout_id', $session['id'])->first();

            if ($payment) {
                $result = PayMongoGateway::statusFromSession($session);
                $this->payments->markPaid($payment, $result['payment_id']);
            }
        }

        // Always acknowledge so PayMongo does not keep retrying events we do not use.
        return response()->json(['received' => true]);
    }
}
