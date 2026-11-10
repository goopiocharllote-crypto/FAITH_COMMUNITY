<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\MassIntention;
use App\Models\MassSchedule;
use App\Models\PrayerRequest;
use App\Models\User;
use App\Notifications\ParishUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RequestCenterController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return $this->requestCenter($user);
    }

    public function donations(Request $request): View
    {
        return view()->file(resource_path('users/donation.blade.php'), [
            'donations' => $request->user()->donations()->latest()->get(),
        ]);
    }

    public function prayerRequests(Request $request): View
    {
        return $this->requestCenter($request->user());
    }

    private function requestCenter(User $user): View
    {
        return view()->file(resource_path('users/my-request.blade.php'), [
            'massIntentions' => $user->massIntentions()->latest()->get(),
            'upcomingSchedules' => MassSchedule::query()
                ->where('is_active', true)
                ->where('mass_date', '>', now())
                ->orderBy('mass_date')
                ->get(),
            'prayerRequests' => $user->prayerRequests()->latest()->get(),
            'donations' => $user->donations()->latest()->get(),
        ]);
    }

    public function storePrayerRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'request' => ['required', 'string', 'max:3000'],
        ]);

        $prayerRequest = $request->user()->prayerRequests()->create($validated);
        $request->user()->notify(ParishUpdate::prayerRequestSubmitted($prayerRequest));

        return redirect()
            ->route('users.my-request')
            ->with('status', __('Your prayer request has been submitted for review.'));
    }

    public function storeVirtualCandlePrayer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'intention_type' => [
                'required',
                'string',
                Rule::in(['Thanksgiving', 'Healing', 'Birthday', 'Souls', 'Special Prayer']),
            ],
            'prayer' => ['required', 'string', 'max:3000'],
        ]);

        $prayerRequest = $request->user()->prayerRequests()->create([
            'title' => 'Virtual Candle - '.$validated['intention_type'],
            'request' => $validated['prayer'],
        ]);
        $request->user()->notify(ParishUpdate::prayerRequestSubmitted($prayerRequest));

        return redirect()
            ->route('users.my-request')
            ->with('status', __('Your virtual candle prayer has been submitted. You can follow its status below.'));
    }

    public function storeDonation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:99999999.99', 'decimal:0,2'],
            'payment_method' => ['required', Rule::in([Donation::METHOD_PARISH, Donation::METHOD_ONLINE])],
            'reference_number' => ['nullable', 'string', 'max:80'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $minimumOnline = (float) config('payments.minimum_online_amount');

        if ($validated['payment_method'] === Donation::METHOD_ONLINE && (float) $validated['amount'] < $minimumOnline) {
            throw ValidationException::withMessages([
                'amount' => __('GCash donations must be at least ₱:amount. Choose "Donate at the parish" for smaller amounts.', [
                    'amount' => number_format($minimumOnline, 2),
                ]),
            ]);
        }

        /** @var Donation $donation */
        $donation = $request->user()->donations()->create($validated);
        $request->user()->notify(ParishUpdate::donationSubmitted($donation));

        if ($donation->isOnline()) {
            return app(PaymentController::class)->redirectToCheckout($donation);
        }

        return redirect()
            ->route('donations.index')
            ->with('status', __('Thank you! Your donation pledge :ref is recorded. Please bring it to the parish office; we will confirm once it is received.', [
                'ref' => $donation->paymentReference(),
            ]));
    }

    public function reviewPrayerRequest(PrayerRequest $prayerRequest, string $status): RedirectResponse
    {
        if ($prayerRequest->status !== 'Pending' || ! in_array($status, ['Approved', 'Rejected'], true)) {
            return back()->withErrors([
                'prayer_request' => 'This prayer request can no longer be updated.',
            ]);
        }

        $prayerRequest->status = $status;
        $prayerRequest->save();
        $prayerRequest->user->notify(ParishUpdate::prayerRequestReviewed($prayerRequest));

        return back()->with('status', 'Prayer request '.strtolower($status).'.');
    }

    public function reviewDonation(Donation $donation, string $status): RedirectResponse
    {
        if ($donation->status !== 'Pending' || ! in_array($status, ['Approved', 'Rejected'], true)) {
            return back()->withErrors([
                'donation' => 'This donation record can no longer be updated.',
            ]);
        }

        $donation->status = $status;

        if ($status === 'Approved') {
            // Admin confirmed the money arrived (cash at the parish, or a GCash transfer they verified).
            $donation->payment_status = 'Paid';
        }

        $donation->save();
        $donation->user->notify(ParishUpdate::donationReviewed($donation));

        return back()->with('status', $status === 'Approved'
            ? 'Donation marked received after verification.'
            : 'Donation record rejected.');
    }
}
