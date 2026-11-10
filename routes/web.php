<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminPageController;
use App\Http\Controllers\MassIntentionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RequestCenterController;
use App\Http\Controllers\UserDashboardController;
use App\Models\Announcement;
use App\Models\Donation;
use App\Models\MassIntention;
use App\Models\MassSchedule;
use App\Models\PrayerRequest;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view()->file(
    resource_path('index.blade.php'),
    [
        'announcements' => Announcement::query()->where('is_active', true)->latest()->get(),
        'livestreams' => MassSchedule::query()
            ->where('is_active', true)
            ->whereNotNull('livestream_url')
            ->where('mass_date', '>=', now()->subHours(2))
            ->orderBy('mass_date')
            ->limit(3)
            ->get(),
    ]
))->name('landing');

Route::redirect('/about', '/#about')->name('landing.about');
Route::redirect('/about-parish', '/#about');
Route::redirect('/services', '/#services')->name('landing.services');
Route::redirect('/parish-services', '/#services');
Route::redirect('/announcements', '/#announcements')->name('landing.announcements');
Route::redirect('/parish-announcements', '/#announcements');
Route::redirect('/contact', '/#contact')->name('landing.contact');
Route::redirect('/parish-contact', '/#contact');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:administrator'])->group(function () {
    Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');
    Route::redirect('/admin/portal', '/admin/dashboard')->name('admin.portal');

    Route::get('/admin/announcements', [AdminPageController::class, 'announcements'])->name('admin.announcements');
    Route::post('/admin/announcements', [AdminPageController::class, 'storeAnnouncement'])
        ->name('admin.announcements.store');
    Route::patch('/admin/announcements/{announcement}', [AdminPageController::class, 'updateAnnouncement'])
        ->name('admin.announcements.update');
    Route::delete('/admin/announcements/{announcement}', [AdminPageController::class, 'destroyAnnouncement'])
        ->name('admin.announcements.destroy');

    Route::get('/admin/mass-intentions', [AdminPageController::class, 'massIntentions'])->name('admin.mass-intentions');
    Route::patch('/admin/mass-intentions/{massIntention}/approve', fn (MassIntention $massIntention, AdminPageController $controller) => $controller->updateMassIntention($massIntention, 'Approved'))
        ->name('admin.mass-intentions.approve');
    Route::patch('/admin/mass-intentions/{massIntention}/reject', fn (MassIntention $massIntention, AdminPageController $controller) => $controller->updateMassIntention($massIntention, 'Rejected'))
        ->name('admin.mass-intentions.reject');
    Route::patch('/admin/mass-intentions/{massIntention}/read-in-church', [AdminPageController::class, 'markMassIntentionReadInChurch'])
        ->name('admin.mass-intentions.read-in-church');
    Route::delete('/admin/mass-intentions/{massIntention}/unread-in-church', [AdminPageController::class, 'unmarkMassIntentionReadInChurch'])
        ->name('admin.mass-intentions.unread-in-church');
    Route::patch('/admin/mass-intentions/{massIntention}/offering-received', [AdminPageController::class, 'markMassIntentionOfferingReceived'])
        ->name('admin.mass-intentions.offering-received');

    Route::get('/admin/schedules', [AdminPageController::class, 'schedules'])->name('admin.schedules');
    Route::post('/admin/schedules', [AdminPageController::class, 'storeSchedule'])
        ->name('admin.schedules.store');
    Route::patch('/admin/schedules/{schedule}', [AdminPageController::class, 'updateSchedule'])
        ->name('admin.schedules.update');
    Route::delete('/admin/schedules/{schedule}', [AdminPageController::class, 'destroySchedule'])
        ->name('admin.schedules.destroy');

    Route::get('/admin/prayer-requests', [AdminPageController::class, 'prayerRequests'])->name('admin.prayer-requests');
    Route::patch('/admin/prayer-requests/{prayerRequest}/approve', fn (PrayerRequest $prayerRequest, RequestCenterController $controller) => $controller->reviewPrayerRequest($prayerRequest, 'Approved'))
        ->name('admin.prayer-requests.approve');
    Route::patch('/admin/prayer-requests/{prayerRequest}/reject', fn (PrayerRequest $prayerRequest, RequestCenterController $controller) => $controller->reviewPrayerRequest($prayerRequest, 'Rejected'))
        ->name('admin.prayer-requests.reject');

    Route::get('/admin/donations', [AdminPageController::class, 'donations'])->name('admin.donations');
    Route::patch('/admin/donations/{donation}/approve', fn (Donation $donation, RequestCenterController $controller) => $controller->reviewDonation($donation, 'Approved'))
        ->name('admin.donations.approve');
    Route::patch('/admin/donations/{donation}/reject', fn (Donation $donation, RequestCenterController $controller) => $controller->reviewDonation($donation, 'Rejected'))
        ->name('admin.donations.reject');

    Route::get('/admin/reports', [AdminPageController::class, 'reports'])->name('admin.reports');
    Route::get('/admin/printlist', [AdminPageController::class, 'printlist'])->name('admin.printlist');
    Route::get('/admin/user-activity', [AdminPageController::class, 'userActivity'])->name('admin.user-activity');
    Route::get('/admin/settings', [AdminPageController::class, 'settings'])->name('admin.settings');
    Route::put('/admin/settings', fn (AdminPageController $controller) => $controller->featureUnavailable('Parish settings'))
        ->name('admin.settings.update');
});

Route::middleware(['auth', 'role:parishioner'])->group(function () {
    Route::get('/dashboard', UserDashboardController::class)->name('dashboard');
    Route::redirect('/users/welcome', '/dashboard')->name('users.welcome');
    Route::get('/mass-intentions', [MassIntentionController::class, 'index'])->name('mass-intentions.index');
    Route::post('/mass-intentions', [MassIntentionController::class, 'store'])->name('mass-intentions.store');
    Route::get('/my-requests', [RequestCenterController::class, 'index'])->name('users.my-request');
    Route::post('/my-requests/prayer-requests', [RequestCenterController::class, 'storePrayerRequest'])
        ->name('users.prayer-requests.store');
    Route::get('/user/prayer-requests', [RequestCenterController::class, 'prayerRequests'])
        ->name('users.prayer-requests.index');

    Route::get('/user/announcements', fn () => view()->file(
        resource_path('users/announcements.blade.php'),
        [
            'announcements' => Announcement::query()
                ->where('is_active', true)
                ->latest()
                ->get(),
            'upcomingSchedules' => MassSchedule::query()
                ->where('is_active', true)
                ->where('mass_date', '>=', now())
                ->orderBy('mass_date')
                ->get(),
        ]
    ))->name('users.announcements');
    Route::get('/user/live-mass', fn () => view()->file(
        resource_path('users/live-mass.blade.php'),
        [
            // Live window: 15 minutes before the Mass until 2 hours after it starts.
            'liveSchedule' => MassSchedule::query()
                ->where('is_active', true)
                ->whereNotNull('livestream_url')
                ->whereBetween('mass_date', [now()->subHours(2), now()->addMinutes(15)])
                ->orderBy('mass_date')
                ->first(),
            'upcomingSchedules' => MassSchedule::query()
                ->where('is_active', true)
                ->where('mass_date', '>=', now())
                ->orderBy('mass_date')
                ->get(),
        ]
    ))
        ->name('users.live-mass');
    Route::post('/virtual-candle', [RequestCenterController::class, 'storeVirtualCandlePrayer'])
        ->name('virtual-candle.store');
    Route::get('/user/requests', fn () => redirect()->route('users.my-request'));
    Route::get('/user/profile', fn () => view()->file(resource_path('users/profile.blade.php')))
        ->name('users.profile');
    Route::get('/virtual-candle', fn () => view()->file(resource_path('users/virtual-candle.blade.php')))
        ->name('virtual-candle');
    Route::get('/donations', [RequestCenterController::class, 'donations'])->name('donations.index');
    Route::redirect('/donation', '/donations')->name('donation');
    Route::post('/donations', [RequestCenterController::class, 'storeDonation'])->name('donations.store');

    // GCash online payments
    Route::post('/donations/{donation}/pay', [PaymentController::class, 'payDonation'])->name('payments.donations.pay');
    Route::post('/mass-intentions/{massIntention}/pay', [PaymentController::class, 'payMassIntention'])->name('payments.mass-intentions.pay');
    Route::get('/payments/{payment}/return', [PaymentController::class, 'return'])->name('payments.return');
    Route::get('/payments/{payment}/gcash-simulator', [PaymentController::class, 'showSimulator'])->name('payments.simulator.show');
    Route::post('/payments/{payment}/gcash-simulator', [PaymentController::class, 'completeSimulator'])->name('payments.simulator.complete');
});

// PayMongo calls this server-to-server, so it has no session/CSRF token (signature-verified instead).
Route::post('/webhooks/paymongo', [PaymentController::class, 'webhook'])->name('payments.webhook');

Route::get('/prayer-requests', fn () => view()->file(
    resource_path('index.blade.php'),
    ['announcements' => collect()]
))->name('prayer-requests.index');
