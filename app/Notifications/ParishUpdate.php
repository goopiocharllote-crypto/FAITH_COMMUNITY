<?php

namespace App\Notifications;

use App\Models\Donation;
use App\Models\MassIntention;
use App\Models\Payment;
use App\Models\PrayerRequest;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirmation / status-update message sent to a parishioner by email and SMS.
 *
 * Only plain strings are stored, so queued messages never depend on records
 * that may change or be deleted before the queue worker runs.
 */
class ParishUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    /** Local-format number (09XXXXXXXXX) to text instead of the account's mobile, e.g. the one typed on a booking form. */
    public ?string $smsTo = null;

    /**
     * @param  list<string>  $lines
     */
    public function __construct(
        public readonly string $kind,
        public readonly string $subject,
        public readonly array $lines,
        public readonly string $sms,
        public readonly ?string $actionText = null,
        public readonly ?string $actionUrl = null,
    ) {
        $this->afterCommit();
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if (! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        if ($this->smsTo || $notifiable->routeNotificationFor('sms', $this)) {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    public function textingNumber(string $localNumber): self
    {
        $this->smsTo = $localNumber;

        return $this;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->subject.' | Faith Community Loboc Parish')
            ->greeting('Hello '.$notifiable->name.',');

        foreach ($this->lines as $line) {
            $mail->line($line);
        }

        if ($this->actionText && $this->actionUrl) {
            $mail->action($this->actionText, $this->actionUrl);
        }

        return $mail->salutation("God bless,\nFaith Community Loboc Parish");
    }

    public function toSms(object $notifiable): string
    {
        return $this->sms.' - Loboc Parish';
    }

    // ------------------------------------------------------------------
    // Mass intentions
    // ------------------------------------------------------------------

    public static function massIntentionSubmitted(MassIntention $intention): self
    {
        $ref = $intention->referenceNumber();
        $when = $intention->mass_date->format('M j, Y g:i A');
        $paymentLine = $intention->isOnline()
            ? 'Please complete your ₱'.self::money($intention->offering_amount).' GCash offering if you have not yet done so.'
            : 'Please bring your ₱'.self::money($intention->offering_amount).' offering to the parish office.';

        return new self(
            'mass_intention.submitted',
            'Mass Intention received ('.$ref.')',
            [
                'We received your Mass Intention request for '.$intention->intention_for.' ('.$intention->intention_type.').',
                'Mass: '.$when.'. Reference no.: '.$ref.'.',
                $paymentLine,
                'We will notify you once the parish office reviews your request.',
            ],
            'Booking confirmation '.$ref.': '.$when.'. Pending approval.',
            'View my Mass Intentions',
            route('mass-intentions.index'),
        );
    }

    public static function massIntentionReviewed(MassIntention $intention): self
    {
        $ref = $intention->referenceNumber();
        $approved = $intention->status === 'Approved';
        $when = $intention->mass_date->format('M j, Y g:i A');

        return new self(
            'mass_intention.'.strtolower($intention->status),
            'Mass Intention '.strtolower($intention->status).' ('.$ref.')',
            $approved
                ? ['Your Mass Intention for '.$intention->intention_for.' has been approved.', 'It will be offered at the Mass on '.$when.'.']
                : ['Your Mass Intention for '.$intention->intention_for.' could not be accepted.', 'Please contact the parish office or submit a new request for another Mass.'],
            $approved
                ? 'Your Mass Intention '.$ref.' is APPROVED for '.$when.'.'
                : 'Your Mass Intention '.$ref.' was not accepted. Please contact the parish office.',
            'View request status',
            route('users.my-request').'#mass-intentions',
        );
    }

    public static function massIntentionReadInChurch(MassIntention $intention): self
    {
        $ref = $intention->referenceNumber();

        return new self(
            'mass_intention.read_in_church',
            'Mass Intention offered ('.$ref.')',
            ['Your Mass Intention for '.$intention->intention_for.' was read during Mass today.', 'Thank you for praying with our parish community.'],
            'Your Mass Intention '.$ref.' for '.$intention->intention_for.' was read at Mass today.',
            'View request status',
            route('users.my-request').'#mass-intentions',
        );
    }

    public static function massIntentionOfferingReceived(MassIntention $intention): self
    {
        $ref = $intention->referenceNumber();

        return new self(
            'mass_intention.offering_received',
            'Offering received ('.$ref.')',
            ['The parish office received your ₱'.self::money($intention->offering_amount).' offering for Mass Intention '.$ref.'.', 'Thank you for your generosity.'],
            'Offering of P'.self::money($intention->offering_amount).' for Mass Intention '.$ref.' received. Thank you!',
            'View my Mass Intentions',
            route('mass-intentions.index'),
        );
    }

    // ------------------------------------------------------------------
    // Prayer requests
    // ------------------------------------------------------------------

    public static function prayerRequestSubmitted(PrayerRequest $prayerRequest): self
    {
        return new self(
            'prayer_request.submitted',
            'Prayer request received',
            ['We received your prayer request "'.$prayerRequest->title.'".', 'Our parish community will keep you in prayer. We will let you know once it has been reviewed.'],
            'Your prayer request "'.self::short($prayerRequest->title).'" was received. We are praying with you.',
            'View my requests',
            route('users.my-request').'#prayer-requests',
        );
    }

    public static function prayerRequestReviewed(PrayerRequest $prayerRequest): self
    {
        $approved = $prayerRequest->status === 'Approved';

        return new self(
            'prayer_request.'.strtolower($prayerRequest->status),
            'Prayer request '.strtolower($prayerRequest->status),
            $approved
                ? ['Your prayer request "'.$prayerRequest->title.'" has been approved and will be included in the parish prayers.']
                : ['Your prayer request "'.$prayerRequest->title.'" was not approved for the parish prayer list.', 'Please contact the parish office if you have questions.'],
            $approved
                ? 'Your prayer request "'.self::short($prayerRequest->title).'" was approved for parish prayers.'
                : 'Your prayer request "'.self::short($prayerRequest->title).'" was not approved. Contact the parish office for help.',
            'View my requests',
            route('users.my-request').'#prayer-requests',
        );
    }

    // ------------------------------------------------------------------
    // Donations and payments
    // ------------------------------------------------------------------

    public static function donationSubmitted(Donation $donation): self
    {
        $ref = $donation->paymentReference();
        $amount = self::money($donation->amount);

        return new self(
            'donation.submitted',
            'Donation pledge received ('.$ref.')',
            $donation->isOnline()
                ? ['Thank you for your ₱'.$amount.' donation pledge (reference '.$ref.').', 'Complete the GCash payment to finish your donation. You will receive a receipt once it is paid.']
                : ['Thank you for your ₱'.$amount.' donation pledge (reference '.$ref.').', 'Please bring your donation to the parish office and mention this reference number. We will confirm once it is received.'],
            $donation->isOnline()
                ? 'Donation '.$ref.' of P'.$amount.' recorded. Complete GCash payment to finish.'
                : 'Donation '.$ref.' of P'.$amount.' recorded. Please give it at the parish office.',
            'View my donations',
            route('donations.index'),
        );
    }

    public static function donationReviewed(Donation $donation): self
    {
        $ref = $donation->paymentReference();
        $received = $donation->status === 'Approved';

        return new self(
            'donation.'.strtolower($donation->status),
            $received ? 'Donation received ('.$ref.')' : 'Donation record not confirmed ('.$ref.')',
            $received
                ? ['The parish received your ₱'.self::money($donation->amount).' donation (reference '.$ref.').', 'Thank you for supporting our parish missions and programs.']
                : ['The parish could not confirm donation '.$ref.'.', 'Please contact the parish office if you believe this is a mistake.'],
            $received
                ? 'Donation '.$ref.' of P'.self::money($donation->amount).' received. Thank you!'
                : 'Donation '.$ref.' could not be confirmed. Please contact the parish office.',
            'View my donations',
            route('donations.index'),
        );
    }

    public static function paymentReceived(Payment $payment): self
    {
        $payable = $payment->payable;
        $ref = $payable->paymentReference();
        $amount = self::money($payment->amount);
        $what = $payable instanceof Donation ? 'donation' : 'Mass Intention offering';

        return new self(
            'payment.received',
            'GCash payment received ('.$ref.')',
            [
                'We received your GCash payment of ₱'.$amount.' for your '.$what.' (reference '.$ref.').',
                'Payment ID: '.($payment->gateway_payment_id ?? $payment->checkout_id).'.',
                'Thank you for your generosity.',
            ],
            'GCash payment of P'.$amount.' for '.$ref.' received. Thank you!',
            'View details',
            $payable->paymentReturnRoute(),
        );
    }

    private static function money(mixed $amount): string
    {
        return number_format((float) $amount, 2);
    }

    private static function short(string $text): string
    {
        return mb_strimwidth($text, 0, 40, '…');
    }
}
