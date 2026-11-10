<?php

namespace App\Http\Controllers;

use App\Models\MassIntention;
use App\Models\Donation;
use App\Models\PrayerRequest;
use App\Models\MassSchedule;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $month = now();

        return view()->file(resource_path('admin/dashboard.blade.php'), [
            'totalMassIntentions' => MassIntention::query()
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count(),
            'pendingRequests' => MassIntention::query()->where('status', 'Pending')->count()
                + PrayerRequest::query()->where('status', 'Pending')->count(),
            'approvedRequests' => MassIntention::query()
                ->where('status', 'Approved')
                ->whereBetween('updated_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count()
                + PrayerRequest::query()
                    ->where('status', 'Approved')
                    ->whereBetween('updated_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->count(),
            'totalDonations' => Donation::query()->where('status', 'Approved')->sum('amount'),
            'upcomingMassSchedule' => MassSchedule::query()
                ->where('is_active', true)
                ->where('mass_date', '>=', now())
                ->orderBy('mass_date')
                ->limit(5)
                ->get(),
            'recentActivities' => (new Collection())
                ->concat(MassIntention::query()->latest()->limit(5)->get()->map(fn (MassIntention $intention): array => [
                    'icon' => '🙏',
                    'title' => 'New Mass Intention: '.$intention->intention_for,
                    'date' => $intention->created_at,
                ]))
                ->concat(PrayerRequest::query()->latest()->limit(5)->get()->map(fn (PrayerRequest $request): array => [
                    'icon' => '🕊',
                    'title' => 'New Prayer Request: '.$request->title,
                    'date' => $request->created_at,
                ]))
                ->concat(Donation::query()->latest()->limit(5)->get()->map(fn (Donation $donation): array => [
                    'icon' => '♡',
                    'title' => 'Donation record submitted: ₱'.number_format((float) $donation->amount, 2),
                    'date' => $donation->created_at,
                ]))
                ->sortByDesc('date')
                ->take(5)
                ->values(),
        ]);
    }
}
