<?php

namespace App\Notifications;

use App\Notifications\Channels\SmsChannel;
use Illuminate\Notifications\Notification;

/** Texts a one-time password reset code. Sent immediately (never queued). */
class PasswordResetCode extends Notification
{
    public ?string $smsTo = null;

    public function __construct(public readonly string $code, public readonly int $minutes)
    {
    }

    public function via(object $notifiable): array
    {
        return [SmsChannel::class];
    }

    public function toSms(object $notifiable): string
    {
        return "Faith Community code: {$this->code}. Valid {$this->minutes} mins. Do not share it. - Loboc Parish";
    }
}
