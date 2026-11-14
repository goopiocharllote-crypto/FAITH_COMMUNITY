<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\MassIntention;
use App\Payments\GcashPayments;
use App\Payments\Payable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * GCash QR payments: show the parish QR code, then take the GCash reference number.
 */
class PaymentController extends Controller
{
    public function __construct(private readonly GcashPayments $gcash)
    {
    }

    public function showMassIntention(Request $request, MassIntention $massIntention): View|RedirectResponse
    {
        return $this->show($request, $massIntention);
    }

    public function showDonation(Request $request, Donation $donation): View|RedirectResponse
    {
        return $this->show($request, $donation);
    }

    public function submitMassIntention(Request $request, MassIntention $massIntention): RedirectResponse
    {
        return $this->submit($request, $massIntention);
    }

    public function submitDonation(Request $request, Donation $donation): RedirectResponse
    {
        return $this->submit($request, $donation);
    }

    private function show(Request $request, Model&Payable $payable): View|RedirectResponse
    {
        abort_unless($payable->user_id === $request->user()->id, 403);

        if ($reason = $this->blockedReason($payable)) {
            return redirect()->to($payable->paymentReturnRoute())->withErrors(['payment' => $reason]);
        }

        return view()->file(resource_path('users/gcash-payment.blade.php'), [
            'payable' => $payable,
            'qrUrl' => GcashPayments::qrImageUrl(),
            'submittedReference' => $payable->isPaymentSubmitted() ? $this->gcash->submittedReference($payable) : null,
            'submitRoute' => $payable instanceof Donation
                ? route('payments.donations.submit', $payable)
                : route('payments.mass-intentions.submit', $payable),
        ]);
    }

    private function submit(Request $request, Model&Payable $payable): RedirectResponse
    {
        abort_unless($payable->user_id === $request->user()->id, 403);

        if ($reason = $this->blockedReason($payable)) {
            return redirect()->to($payable->paymentReturnRoute())->withErrors(['payment' => $reason]);
        }

        $reference = GcashPayments::normalizeReference((string) $request->input('reference_number'));
        $request->merge(['reference_number' => $reference]);

        $request->validate([
            'reference_number' => ['required', 'regex:/^\d{13}$/'],
        ], [
            'reference_number.required' => __('Enter the reference number from your GCash receipt.'),
            'reference_number.regex' => __('The GCash reference number has 13 digits. Check your GCash receipt and try again.'),
        ]);

        if ($this->gcash->referenceTakenByAnother($reference, $payable)) {
            throw ValidationException::withMessages([
                'reference_number' => __('This GCash reference number was already used for another payment.'),
            ]);
        }

        $this->gcash->submitReference($payable, $reference);

        return redirect()
            ->to($payable->paymentReturnRoute())
            ->with('status', __('Thank you! We received your GCash reference number :reference. The parish office will confirm your payment shortly.', [
                'reference' => $reference,
            ]));
    }

    /** Why this record cannot be paid right now, or null when it can. */
    private function blockedReason(Model&Payable $payable): ?string
    {
        if (! $payable->isOnline() || $payable->paymentAmount() <= 0) {
            return __('This record has no GCash payment due.');
        }

        if ($payable->isPaid()) {
            return __('This record is already paid. Thank you!');
        }

        if ($payable->status === 'Rejected') {
            return __('This request was not accepted, so no payment is needed.');
        }

        return null;
    }
}
