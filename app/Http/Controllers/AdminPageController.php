<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Announcement;
use App\Models\Donation;
use App\Models\MassIntention;
use App\Models\MassSchedule;
use App\Models\PrayerRequest;
use App\Models\User;
use App\Notifications\ParishUpdate;
use App\Payments\GcashPayments;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPageController extends Controller
{
    public function announcements(): View
    {
        return $this->page('announcements', [
            'announcements' => Announcement::query()->latest()->get(),
        ]);
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:10000'],
            'announcement_date' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        Announcement::create($validated);

        return redirect()
            ->route('admin.announcements')
            ->with('status', __('Announcement created successfully.'));
    }

    public function updateAnnouncement(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:10000'],
            'announcement_date' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $announcement->update($validated);

        return redirect()
            ->route('admin.announcements')
            ->with('status', __('Announcement updated successfully.'));
    }

    public function destroyAnnouncement(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()
            ->route('admin.announcements')
            ->with('status', __('Announcement deleted successfully.'));
    }

    public function massIntentions(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['Pending', 'Approved', 'Rejected'])],
        ]);
        $statusCounts = MassIntention::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $massIntentions = MassIntention::query()
            ->with('user')
            ->when(
                $filters['status'] ?? null,
                fn ($query, string $status) => $query->where('status', $status)
            )
            ->orderByRaw("CASE WHEN status = 'Pending' THEN 0 WHEN status = 'Approved' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->page('mass-intentions', [
            'massIntentions' => $massIntentions,
            'statusCounts' => $statusCounts,
            'activeStatus' => $filters['status'] ?? null,
        ]);
    }

    public function schedules(): View
    {
        return $this->page('schedules', [
            'schedules' => MassSchedule::query()->orderBy('mass_date')->get(),
        ]);
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'mass_date' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],
            'livestream_url' => ['nullable', 'url:https', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $massDate = Carbon::createFromFormat('Y-m-d\TH:i', $validated['mass_date']);
        $validated['mass_date'] = $massDate->format('Y-m-d H:i:s');
        // Requests always close a fixed number of minutes before the Mass starts.
        $validated['closing_at'] = $massDate->copy()
            ->subMinutes(MassSchedule::CLOSING_MINUTES_BEFORE_MASS)
            ->format('Y-m-d H:i:s');
        $validated['is_active'] = $request->boolean('is_active');

        MassSchedule::query()->create($validated);

        return redirect()
            ->route('admin.schedules')
            ->with('status', __('Mass schedule created successfully.'));
    }

    public function updateSchedule(Request $request, MassSchedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'mass_date' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],
            'livestream_url' => ['nullable', 'url:https', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $massDate = Carbon::createFromFormat('Y-m-d\TH:i', $validated['mass_date']);
        $validated['mass_date'] = $massDate->format('Y-m-d H:i:s');
        // Requests always close a fixed number of minutes before the Mass starts.
        $validated['closing_at'] = $massDate->copy()
            ->subMinutes(MassSchedule::CLOSING_MINUTES_BEFORE_MASS)
            ->format('Y-m-d H:i:s');
        $validated['is_active'] = $request->boolean('is_active');
        $schedule->update($validated);

        return redirect()
            ->route('admin.schedules')
            ->with('status', __('Mass schedule updated successfully.'));
    }

    public function destroySchedule(MassSchedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()
            ->route('admin.schedules')
            ->with('status', __('Mass schedule deleted successfully.'));
    }

    public function prayerRequests(): View
    {
        return $this->page('prayer-requests', [
            'prayerRequests' => PrayerRequest::query()->with('user')->latest()->get(),
        ]);
    }

    public function donations(): View
    {
        $donations = Donation::query()->with(['user', 'payments', 'payments.receipt:id,payment_id,mime,size'])->latest()->get();

        return $this->page('donations', [
            'donations' => $donations,
            'totalDonations' => Donation::query()->where('status', 'Approved')->sum('amount'),
        ]);
    }

    public function reports(Request $request): View
    {
        $filters = $request->validate([
            'report_type' => ['nullable', Rule::in(['mass_intention', 'donation', 'prayer_request'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $intentionsQuery = MassIntention::query();

        if (! empty($filters['start_date'])) {
            $intentionsQuery->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $intentionsQuery->whereDate('created_at', '<=', $filters['end_date']);
        }

        $intentions = $intentionsQuery->latest()->get();
        $total = $intentions->count();
        $topIntentions = $intentions
            ->groupBy('intention_type')
            ->map(fn (Collection $group, string $type): array => [
                'type' => $type,
                'count' => $group->count(),
                'percentage' => $total === 0 ? 0 : (int) round($group->count() / $total * 100),
            ])
            ->values();

        $weeklyData = $intentions
            ->groupBy(fn (MassIntention $intention): string => $intention->created_at->startOfWeek()->format('M d'))
            ->map(fn (Collection $group): int => $group->count());

        return $this->page('reports', [
            'reportType' => $filters['report_type'] ?? 'mass_intention',
            'startDate' => $filters['start_date'] ?? '',
            'endDate' => $filters['end_date'] ?? '',
            'totalMassIntentions' => $total,
            'pendingRequests' => $intentions->where('status', 'Pending')->count(),
            'approvedRequests' => $intentions->where('status', 'Approved')->count(),
            'totalDonations' => Donation::query()->where('status', 'Approved')->sum('amount'),
            'topIntentions' => $topIntentions,
            'weeklyData' => $weeklyData,
        ]);
    }

    public function printlist(Request $request): View
    {
        $validated = $request->validate([
            'category' => ['nullable', Rule::in(['All', 'Thanksgiving', 'Healing', 'Souls', 'Birthday', 'Special Prayer'])],
        ]);
        $category = $validated['category'] ?? 'All';
        $massIntentions = MassIntention::query()
            ->where('status', 'Approved')
            ->when($category !== 'All', fn ($query) => $query->where('intention_type', $category))
            ->orderBy('mass_date')
            ->get();

        return $this->page('printlist', [
            'category' => $category,
            'massIntentions' => $massIntentions,
        ]);
    }

    public function userActivity(): View
    {
        return $this->page('user-activity', [
            'activeUsers' => collect(),
            'users' => User::query()->orderBy('name')->get(),
            'loginHistory' => collect(),
        ]);
    }

    public function settings(): View
    {
        return $this->page('settings');
    }

    public function updateMassIntention(MassIntention $massIntention, string $status): RedirectResponse
    {
        if ($massIntention->status !== 'Pending' || ! in_array($status, ['Approved', 'Rejected'], true)) {
            return back()->withErrors([
                'mass_intention' => 'This Mass Intention can no longer be updated.',
            ]);
        }

        $massIntention->status = $status;
        $massIntention->save();
        $massIntention->user->notify(ParishUpdate::massIntentionReviewed($massIntention));

        return back()->with('mass_success', 'Mass Intention '.strtolower($status).' successfully.');
    }

    public function markMassIntentionReadInChurch(MassIntention $massIntention): RedirectResponse
    {
        if ($massIntention->status !== 'Approved') {
            return back()->withErrors([
                'mass_intention' => 'Only approved Mass Intentions can be marked as read in church.',
            ]);
        }

        if ($massIntention->church_read_at !== null) {
            return back()->withErrors([
                'mass_intention' => 'This Mass Intention is already marked as read in church.',
            ]);
        }

        $massIntention->church_read_at = now();
        $massIntention->save();
        $massIntention->user->notify(ParishUpdate::massIntentionReadInChurch($massIntention));

        return back()->with('mass_success', 'Mass Intention marked as read in church.');
    }

    /** Pay-at-parish offering handed to the parish office. */
    public function markMassIntentionOfferingReceived(MassIntention $massIntention): RedirectResponse
    {
        if ($massIntention->isPaid()) {
            return back()->withErrors([
                'mass_intention' => 'This offering is already marked as received.',
            ]);
        }

        if ($massIntention->isOnline() && ! $massIntention->isPaymentSubmitted()) {
            return back()->withErrors([
                'mass_intention' => 'The parishioner has not submitted a GCash reference number yet.',
            ]);
        }

        $massIntention->markAsPaid();
        app(GcashPayments::class)->recordConfirmed($massIntention);
        $massIntention->user->notify(ParishUpdate::massIntentionOfferingReceived($massIntention));

        return back()->with('mass_success', 'Offering marked as received.');
    }

    public function unmarkMassIntentionReadInChurch(MassIntention $massIntention): RedirectResponse
    {
        if ($massIntention->status !== 'Approved' || $massIntention->church_read_at === null) {
            return back()->withErrors([
                'mass_intention' => 'This Mass Intention is not marked as read in church.',
            ]);
        }

        $massIntention->church_read_at = null;
        $massIntention->save();

        return back()->with('mass_success', 'Mass Intention marked as not read in church.');
    }

    public function featureUnavailable(string $feature): RedirectResponse
    {
        return back()->withErrors([
            'admin_feature' => "{$feature} changes are unavailable because this feature has no database storage configured yet.",
        ]);
    }

    private function page(string $name, array $data = []): View
    {
        return view()->file(resource_path("admin/{$name}.blade.php"), $data);
    }
}
