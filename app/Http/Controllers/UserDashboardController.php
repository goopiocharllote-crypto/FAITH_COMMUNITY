<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\MassSchedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $massIntentions = $user->massIntentions();

        return view()->file(resource_path('users/dashboard.blade.php'), [
            'newUser' => (bool) $request->session()->pull('newUser', false),
            'activeMassIntentions' => (clone $massIntentions)->where('status', 'Pending')->count(),
            'donationThisYear' => 0,
            'myRequests' => (clone $massIntentions)->count(),
            'upcomingSchedules' => MassSchedule::query()
                ->where('is_active', true)
                ->where('mass_date', '>=', now())
                ->orderBy('mass_date')
                ->limit(10)
                ->get(),
            'latestAnnouncements' => Announcement::query()
                ->where('is_active', true)
                ->latest()
                ->limit(3)
                ->get(),
            'massIntentions' => (clone $massIntentions)->latest()->get(),
        ]);
    }
}
