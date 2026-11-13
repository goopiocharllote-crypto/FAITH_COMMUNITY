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
 * SMS_DRIVER=iprog      → sent through iProg SMS (https://www.iprogsms.com)
 */
class SmsChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $number = ($notification->smsTo ?? null) ?: $notifiable->routeNotificationFor('sms', $notification);

        if (! $number || ! method_exists($notification, 'toSms')) {
            return;
        }

        $message = (string) $notification->toSms($notifiable);

        $driver = config('services.sms.driver');

        if ($driver === 'iprog') {
            $this->sendThroughIprog($number, $message);

            return;
        }

        if ($driver !== 'semaphore') {
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

    private function sendThroughIprog(string $number, string $message): void
    {
        // iProg examples use the international format (639XXXXXXXXX); we store 09XXXXXXXXX.
        $international = preg_replace('/^0/', '63', $number);

        // iProg adds the approved header ("Faith Community") to every message, and the free tier
        // allows only 100 characters, so drop our own "- Loboc Parish" sign-off.
        $message = preg_replace('/ - Loboc Parish$/', '', $message);

        try {
            $response = Http::asForm()->timeout(15)->post(config('services.iprog.url'), [
                'api_token' => config('services.iprog.api_token'),
                'phone_number' => $international,
                'message' => $message,
            ]);

            // iProg answers {"status": 200, ...} on success; anything else is a failure.
            if ($response->failed() || (int) $response->json('status', 200) !== 200) {
                Log::error('iProg SMS failed', ['number' => $number, 'status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (Throwable $exception) {
            // An SMS outage must not block the email copy or the parishioner's request.
            Log::error('iProg SMS failed', ['number' => $number, 'error' => $exception->getMessage()]);
        }
    }
}
