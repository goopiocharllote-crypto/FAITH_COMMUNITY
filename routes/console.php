<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Also reject expired requests on hosts that run the scheduler (the web pages do it lazily).
Schedule::call(fn () => App\Models\MassIntention::rejectExpired())->everyMinute()->name('reject-expired-mass-intentions');
