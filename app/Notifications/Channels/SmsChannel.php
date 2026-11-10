<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends notification text messages.
 *
 * SMS_DRIVER=log        → written to storage/logs/laravel.log (default, no cost)
 * SMS_DRIVER=semaphore  → sent through Semaphore (https://semaphore.co)
 */
class SmsChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $number = $notifiable->routeNotificationFor('sms', $notification);

        if (! $number || ! method_exists($notification, 'toSms')) {
            return;
        }

        $message = (string) $notification->toSms($notifiable);

        if (config('services.sms.driver') !== 'semaphore') {
            Log::info('[SMS to '.$number.'] '.$message);

            return;
        }

        try {
            $response = Http::asForm()->timeout(15)->post(config('services.semaphore.url'), array_filter([
                'apikey' => config('services.semaphore.api_key'),
                'number' => $number,
                'message' => $message,
                'sendername' => config('services.semaphore.sender_name'),
            ]));

            if ($response->failed()) {
                Log::error('Semaphore SMS failed', ['number' => $number, 'status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (Throwable $exception) {
            // An SMS outage must not block the email copy or the parishioner's request.
            Log::error('Semaphore SMS failed', ['number' => $number, 'error' => $exception->getMessage()]);
        }
    }
}
