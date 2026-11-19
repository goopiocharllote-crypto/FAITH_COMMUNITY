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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        MassIntention::rejectExpired();

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
            'activeUsers' => User::query()
                ->where('role', User::ROLE_PARISHIONER)
                ->where('last_activity_at', '>=', now()->subMinutes(2))
                ->orderByDesc('last_activity_at')
                ->get(),
            'users' => User::query()->where('role', User::ROLE_PARISHIONER)->orderBy('name')->get(),
            'loginHistory' => \App\Models\LoginLog::query()
                ->with('user')
                ->latest('login_at')
                ->limit(50)
                ->get(),
        ]);
    }

    public function accounts(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'deactivated'])],
        ]);
        $search = trim((string) ($filters['search'] ?? ''));
        $status = $filters['status'] ?? null;

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%';
                $mobile = ltrim(preg_replace('/\D/', '', $search), '0');

                $query->where(function ($query) use ($like, $mobile) {
                    $query->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);

                    if ($mobile !== '') {
                        $query->orWhere('mobile', 'like', '%'.$mobile.'%');
                    }
                });
            })
            ->when($status === 'active', fn ($query) => $query->whereNull('deactivated_at'))
            ->when($status === 'deactivated', fn ($query) => $query->whereNotNull('deactivated_at'))
            ->withCount(['massIntentions', 'prayerRequests', 'donations'])
            // Administrators first, then parishioners by newest sign-up.
            ->orderByRaw('CASE WHEN role = ? THEN 0 ELSE 1 END', [User::ROLE_ADMINISTRATOR])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return $this->page('accounts', [
            'users' => $users,
            'search' => $search,
            'activeStatus' => $status,
            'counts' => [
                'all' => User::query()->count(),
                'active' => User::query()->whereNull('deactivated_at')->count(),
                'deactivated' => User::query()->whereNotNull('deactivated_at')->count(),
            ],
        ]);
    }

    public function deactivateAccount(Request $request, User $user): RedirectResponse
    {
        if ($blocked = $this->protectedAccount($request, $user)) {
            return $blocked;
        }

        $user->forceFill(['deactivated_at' => now(), 'remember_token' => Str::random(60)])->save();

        return back()->with('accounts_success', "{$user->name}'s account has been deactivated. They have been signed out and can no longer log in.");
    }

    public function reactivateAccount(Request $request, User $user): RedirectResponse
    {
        if ($blocked = $this->protectedAccount($request, $user)) {
            return $blocked;
        }

        $user->forceFill(['deactivated_at' => null])->save();

        return back()->with('accounts_success', "{$user->name}'s account has been reactivated. They can log in again.");
    }

    public function confirmDeleteAccount(Request $request, User $user): View|RedirectResponse
    {
        if ($blocked = $this->protectedAccount($request, $user)) {
            return $blocked;
        }

        $user->loadCount(['massIntentions', 'prayerRequests', 'donations']);

        return $this->page('accounts-delete', ['account' => $user]);
    }

    /**
     * Deleting keeps the parish records: the user row is soft deleted, so the
     * Mass Intentions, prayer requests, donations and GCash payments that point to it
     * (with cascading foreign keys) are left in place and still show the parishioner's name.
     * Their sign-in details and contact info are removed, which frees the email and
     * mobile number to be registered again and stops any further notifications.
     */
    public function destroyAccount(Request $request, User $user): RedirectResponse
    {
        if ($blocked = $this->protectedAccount($request, $user)) {
            return $blocked;
        }

        $request->validate([
            'confirm_name' => ['required', 'string', function ($attribute, $value, $fail) use ($user) {
                if (mb_strtolower(trim($value)) !== mb_strtolower(trim($user->name))) {
                    $fail(__('Type the account name exactly as shown to confirm.'));
                }
            }],
        ]);

        $name = $user->name;

        DB::transaction(function () use ($user) {
            if ($user->mobile) {
                DB::table('password_reset_codes')->where('mobile', $user->mobile)->delete();
            }
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            \App\Models\LoginLog::query()->where('user_id', $user->id)->whereNull('logout_at')->update(['logout_at' => now()]);

            $user->forceFill([
                'email' => "deleted-user-{$user->id}@deleted.invalid",
                'mobile' => null,
                'password' => Str::random(60),
                'remember_token' => Str::random(60),
                'deactivated_at' => $user->deactivated_at ?? now(),
            ])->save();

            $user->delete();
        });

        return redirect()->route('admin.accounts')
            ->with('accounts_success', "{$name}'s account has been deleted. Their Mass Intentions, prayer requests and donations remain in the parish records.");
    }

    /** Administrators cannot deactivate or delete themselves or another administrator. */
    private function protectedAccount(Request $request, User $user): ?RedirectResponse
    {
        if ($user->is($request->user())) {
            return redirect()->route('admin.accounts')->withErrors(['accounts' => __('You cannot change your own account here.')]);
        }

        if ($user->isAdministrator()) {
            return redirect()->route('admin.accounts')->withErrors(['accounts' => __('Administrator accounts cannot be deactivated or deleted.')]);
        }

        return null;
    }

    public function settings(): View
    {
        return $this->page('settings', ['settings' => \App\Models\ParishSetting::values()]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'parish_name' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\s-]{7,30}$/'],
            'email' => ['required', 'string', 'email', 'max:120'],
            'timezone' => ['required', Rule::in(['Asia/Manila', 'Asia/Tokyo', 'UTC'])],
            'pending_expiry_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ], [
            'contact_number.regex' => 'Enter a valid contact number (digits, spaces, +, - or parentheses).',
        ]);

        \App\Models\ParishSetting::put($data);

        return redirect()->route('admin.settings')->with('settings_success', 'Parish settings saved successfully.');
    }

    public function updateMassIntention(MassIntention $massIntention, string $status): RedirectResponse
    {
        MassIntention::rejectExpired();
        $massIntention->refresh();

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
