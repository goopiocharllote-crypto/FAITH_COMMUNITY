<?php

namespace App\Http\Controllers;

use App\Models\MassIntention;
use App\Models\MassSchedule;
use App\Notifications\ParishUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MassIntentionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view()->file(resource_path('users/mass-intentions.blade.php'), [
            'massIntentions' => $user->massIntentions()
                ->latest()
                ->get(),
            'upcomingSchedules' => MassSchedule::query()
                ->where('is_active', true)
                ->where('mass_date', '>', now())
                ->orderBy('mass_date')
                ->get(),
            'requestedScheduleIds' => $user->massIntentions()
                ->where('status', '!=', 'Rejected')
                ->whereNotNull('schedule_id')
                ->pluck('schedule_id')
                ->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'intention_type' => [
                'required',
                'string',
                Rule::in(['Thanksgiving', 'Healing', 'Birthday', 'Souls', 'Special Prayer']),
            ],
            'intention_for' => ['required', 'string', 'max:255'],
            'additional_message' => ['nullable', 'string', 'max:2000'],
            'schedule_id' => [
                'required',
                'integer',
                Rule::exists('mass_schedules', 'id')->where('is_active', true),
            ],
            'mobile' => ['required', 'string', 'regex:/^9[0-9]{9}$/'],
            'offering_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'payment_method' => ['nullable', Rule::in([MassIntention::METHOD_ONLINE, MassIntention::METHOD_PARISH])],
        ]);

        $validated['payment_method'] ??= MassIntention::METHOD_PARISH;

        $schedule = MassSchedule::query()->findOrFail($validated['schedule_id']);

        if (
            ! $schedule->mass_date->isFuture()
            || ! $schedule->closing_at
            || ! $schedule->closing_at->isFuture()
            || $schedule->closing_at->greaterThanOrEqualTo($schedule->mass_date)
        ) {
            throw ValidationException::withMessages([
                'schedule_id' => __('Intention requests for this Mass are closed. Choose another upcoming Mass schedule.'),
            ]);
        }

        $alreadyRequested = $request->user()->massIntentions()
            ->where('status', '!=', 'Rejected')
            ->where(fn ($query) => $query
                ->where('schedule_id', $schedule->id)
                ->orWhere('mass_date', $schedule->mass_date))
            ->exists();

        if ($alreadyRequested) {
            throw ValidationException::withMessages([
                'schedule_id' => __('You already have a Mass Intention request for this Mass date and time. Choose another Mass schedule.'),
            ]);
        }

        $validated['mass_date'] = $schedule->mass_date;
        /** @var MassIntention $intention */
        $intention = $request->user()->massIntentions()->create($validated);
        // The booking confirmation is texted to the mobile number entered on the booking form.
        $request->user()->notify(
            ParishUpdate::massIntentionSubmitted($intention)->textingNumber('0'.$intention->mobile)
        );

        if ($intention->isOnline() && (float) $intention->offering_amount > 0) {
            return redirect()
                ->route('payments.mass-intentions.pay', $intention)
                ->with('status', __('Your Mass Intention request has been submitted. Scan the GCash QR code below to pay your offering.'));
        }

        return redirect()
            ->route('mass-intentions.confirmation', $intention)
            ->with('status', __('Your Mass Intention request has been submitted.'));
    }

    /** Booking confirmation slip for one of the parishioner's own Mass intentions. */
    public function confirmation(Request $request, MassIntention $massIntention): View
    {
        abort_unless($massIntention->user_id === $request->user()->id, 403);

        return view()->file(resource_path('users/mass-intention-confirmation.blade.php'), [
            'massIntention' => $massIntention->load('schedule'),
        ]);
    }
}
