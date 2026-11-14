<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\MassIntention;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\GcashReceiptUploaded;
use App\Payments\GcashPayments;
use App\Payments\Payable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * GCash QR payments: show the parish QR code, then take the GCash receipt the parishioner uploads.
 */
class PaymentController extends Controller
{
    /** Largest receipt picture accepted, in kilobytes. */
    public const MAX_RECEIPT_KB = 2048;

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

    /** The receipt picture. Only the parishioner who paid and the administrators may open it. */
    public function receipt(Request $request, Payment $payment): Response
    {
        $user = $request->user();

        abort_unless(
            $user->role === User::ROLE_ADMINISTRATOR || $payment->user_id === $user->id,
            403,
        );

        $receipt = $payment->receipt;
        abort_if($receipt === null, 404);

        return response($receipt->contents(), 200, [
            'Content-Type' => $receipt->mime,
            'Content-Disposition' => 'inline; filename="gcash-receipt-'.$payment->id.'"',
            'Cache-Control' => 'private, max-age=0, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
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
            'submittedPayment' => $payable->isPaymentSubmitted() ? $this->gcash->latestPayment($payable) : null,
            'maxReceiptMb' => self::MAX_RECEIPT_KB / 1024,
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
        $request->merge(['reference_number' => $reference !== '' ? $reference : null]);

        $request->validate([
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_RECEIPT_KB],
            'reference_number' => ['nullable', 'regex:/^\d{13}$/'],
        ], [
            'receipt.required' => __('Upload a picture or screenshot of your GCash receipt.'),
            'receipt.uploaded' => __('The receipt could not be uploaded. Use a picture smaller than :mb MB.', ['mb' => self::MAX_RECEIPT_KB / 1024]),
            'receipt.mimes' => __('The receipt must be a picture (JPG, PNG or WEBP).'),
            'receipt.max' => __('The receipt picture is too big. Use a picture smaller than :mb MB.', ['mb' => self::MAX_RECEIPT_KB / 1024]),
            'reference_number.regex' => __('The GCash reference number has 13 digits. Check your GCash receipt, or leave it empty.'),
        ]);

        if ($reference !== '' && $this->gcash->referenceTakenByAnother($reference, $payable)) {
            throw ValidationException::withMessages([
                'reference_number' => __('This GCash reference number was already used for another payment.'),
            ]);
        }

        $payment = $this->gcash->submitReceipt($payable, $request->file('receipt'), $reference !== '' ? $reference : null);

        Notification::send(
            User::query()->where('role', User::ROLE_ADMINISTRATOR)->whereNotNull('email')->get(),
            GcashReceiptUploaded::forPayment($payment->load(['payable', 'user'])),
        );

        return redirect()
            ->to($payable->paymentReturnRoute())
            ->with('status', __('Thank you! We received your GCash receipt. The parish office will check it and confirm your payment shortly.'));
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
