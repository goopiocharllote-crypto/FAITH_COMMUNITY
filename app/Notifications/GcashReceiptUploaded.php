<?php

namespace App\Notifications;

use App\Models\Donation;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email to the parish administrators when a parishioner uploads a GCash receipt.
 * Only plain strings are stored so a queued copy never depends on records that may change.
 */
class GcashReceiptUploaded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $subject,
        public readonly string $summary,
        public readonly string $details,
        public readonly string $url,
    ) {
        $this->afterCommit();
    }

    public static function forPayment(Payment $payment): self
    {
        $payable = $payment->payable;
        $isDonation = $payable instanceof Donation;
        $ref = $payable->paymentReference();
        $amount = number_format((float) $payment->amount, 2);

        return new self(
            'GCash receipt uploaded ('.$ref.')',
            $payment->user->name.' uploaded a GCash receipt for a '.($isDonation ? 'donation' : 'Mass Intention offering').' ('.$ref.').',
            'Amount to match: ₱'.$amount.($payment->gateway_payment_id ? '. GCash reference no.: '.$payment->gateway_payment_id : '').'.',
            route($isDonation ? 'admin.donations' : 'admin.mass-intentions'),
        );
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ! empty($notifiable->email) ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject.' | Faith Community Loboc Parish')
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->summary)
            ->line($this->details)
            ->line('Open the receipt, compare it with the parish GCash record, then confirm the payment.')
            ->action('Review the payment', $this->url)
            ->salutation("God bless,\nFaith Community Loboc Parish");
    }
}
