<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Emails the 6-digit code that proves a new sign-up owns its email address. Sent immediately (never queued). */
class EmailVerificationCode extends Notification
{
    public function __construct(public readonly string $code, public readonly int $minutes)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Faith Community verification code')
            ->greeting('Welcome to Faith Community!')
            ->line('Use this code to verify your email address and finish creating your account:')
            ->line('**'.$this->code.'**')
            ->line("The code is valid for {$this->minutes} minutes. Please do not share it with anyone.")
            ->line('If you did not try to create an account, you can safely ignore this email.')
            ->salutation('God bless, Parish of Saint Peter the Apostle, Loboc');
    }
}
