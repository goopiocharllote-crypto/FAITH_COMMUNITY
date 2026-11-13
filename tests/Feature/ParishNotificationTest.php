<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\MassIntention;
use App\Models\MassSchedule;
use App\Models\PrayerRequest;
use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\ParishUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ParishNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mass_intention_lifecycle_sends_email_and_sms_updates(): void
    {
        Notification::fake();
        $user = User::factory()->create(['mobile' => '9171234567']);
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
        $schedule = MassSchedule::query()->create([
            'title' => 'Sunday Mass',
            'mass_date' => now()->addDays(3)->setTime(8, 0),
            'closing_at' => now()->addDays(2),
            'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('mass-intentions.store'), [
            'intention_type' => 'Thanksgiving',
            'intention_for' => 'The Santos family',
            'schedule_id' => $schedule->id,
            'mobile' => '9171234567',
            'offering_amount' => '100',
            'payment_method' => 'Cash',
        ]);
        $intention = MassIntention::query()->sole();

        Notification::assertSentTo($user, ParishUpdate::class, function (ParishUpdate $n, array $channels) use ($intention) {
            return $n->kind === 'mass_intention.submitted'
                && $channels === ['mail', SmsChannel::class]
                && str_contains($n->subject, $intention->referenceNumber())
                && collect($n->lines)->contains(fn ($line) => str_contains($line, 'bring your ₱100.00 offering to the parish office'));
        });

        $this->actingAs($admin)->patch(route('admin.mass-intentions.approve', $intention));
        Notification::assertSentTo($user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'mass_intention.approved'
            && str_contains($n->sms, 'APPROVED'));

        $this->actingAs($admin)->patch(route('admin.mass-intentions.read-in-church', $intention));
        Notification::assertSentTo($user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'mass_intention.read_in_church');

        Notification::assertSentToTimes($user, ParishUpdate::class, 3);
    }

    public function test_rejected_intention_notifies_the_parishioner(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
        $intention = MassIntention::factory()->create();

        $this->actingAs($admin)->patch(route('admin.mass-intentions.reject', $intention));

        Notification::assertSentTo($intention->user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'mass_intention.rejected');
    }

    public function test_prayer_requests_and_virtual_candles_send_confirmation_and_review_updates(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

        $this->actingAs($user)->post(route('users.prayer-requests.store'), [
            'title' => 'Healing for my mother',
            'request' => 'Please pray for her recovery.',
        ]);
        $this->actingAs($user)->post(route('virtual-candle.store'), [
            'intention_type' => 'Healing',
            'prayer' => 'Lord, grant comfort.',
        ]);

        Notification::assertSentToTimes($user, ParishUpdate::class, 2);
        Notification::assertSentTo($user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'prayer_request.submitted');

        $prayerRequest = PrayerRequest::query()->where('title', 'Healing for my mother')->sole();
        $this->actingAs($admin)->patch(route('admin.prayer-requests.approve', $prayerRequest));

        Notification::assertSentTo($user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'prayer_request.approved');
    }

    public function test_admin_confirming_a_parish_donation_marks_it_paid_and_notifies(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

        $this->actingAs($user)->post(route('donations.store'), ['amount' => '500', 'payment_method' => 'Cash']);
        $donation = Donation::query()->sole();

        Notification::assertSentTo($user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'donation.submitted'
            && collect($n->lines)->contains(fn ($line) => str_contains($line, 'bring your donation to the parish office')));

        $this->actingAs($admin)->patch(route('admin.donations.approve', $donation));

        $this->assertSame('Paid', $donation->fresh()->payment_status);
        Notification::assertSentTo($user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'donation.approved');
    }

    public function test_users_without_a_mobile_number_only_get_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['mobile' => null]);

        $this->actingAs($user)->post(route('users.prayer-requests.store'), ['title' => 'Peace', 'request' => 'For peace.']);

        Notification::assertSentTo($user, ParishUpdate::class, fn (ParishUpdate $n, array $channels) => $channels === ['mail']);
    }

    public function test_email_contains_the_update_and_a_link_back_to_the_portal(): void
    {
        $user = User::factory()->create(['name' => 'Maria Clara', 'mobile' => '9171234567']);
        $intention = MassIntention::factory()->create(['user_id' => $user->id, 'payment_method' => 'GCash', 'offering_amount' => 100]);

        $mail = ParishUpdate::massIntentionSubmitted($intention)->toMail($user);
        $html = (string) $mail->render();

        $this->assertStringContainsString($intention->referenceNumber(), $mail->subject);
        $this->assertStringContainsString('Hello Maria Clara', $html);
        $this->assertStringContainsString('complete your ₱100.00 GCash offering', $html);
        $this->assertSame(route('mass-intentions.index'), $mail->actionUrl);
    }

    public function test_booking_confirmation_sms_goes_to_the_number_entered_on_the_booking_form(): void
    {
        Notification::fake();

        $user = User::factory()->create(['mobile' => '9171234567']);
        $schedule = MassSchedule::query()->create([
            'title' => 'Sunday Mass',
            'mass_date' => now()->addDays(3),
            'closing_at' => now()->addDays(2),
            'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('mass-intentions.store'), [
            'intention_type' => 'Thanksgiving',
            'intention_for' => 'My family',
            'schedule_id' => $schedule->id,
            'mobile' => '9998887777',
            'offering_amount' => '100.00',
        ]);

        Notification::assertSentTo($user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'mass_intention.submitted'
            && $n->smsTo === '09998887777'
            && str_contains($n->sms, 'Booking confirmation'));
    }

    public function test_sms_log_driver_writes_the_message_to_the_log(): void
    {
        config(['services.sms.driver' => 'log']);
        Log::spy();
        $user = User::factory()->create(['mobile' => '9171234567']);

        (new SmsChannel)->send($user, new ParishUpdate('test', 'Subject', ['Line'], 'Your request was received.'));

        Log::shouldHaveReceived('info')->once()->with('[SMS to 09171234567] Your request was received. - Loboc Parish');
    }

    public function test_sms_semaphore_driver_posts_to_semaphore(): void
    {
        config([
            'services.sms.driver' => 'semaphore',
            'services.semaphore.api_key' => 'sem_key',
            'services.semaphore.sender_name' => 'LOBOCPARISH',
            'services.semaphore.url' => 'https://api.semaphore.co/api/v4/messages',
        ]);
        Http::fake(['api.semaphore.co/*' => Http::response([['message_id' => 1, 'status' => 'Queued']])]);
        $user = User::factory()->create(['mobile' => '9171234567']);

        (new SmsChannel)->send($user, new ParishUpdate('test', 'Subject', ['Line'], 'Approved for Sunday.'));

        Http::assertSent(fn (HttpRequest $request) => $request->url() === 'https://api.semaphore.co/api/v4/messages'
            && $request['apikey'] === 'sem_key'
            && $request['number'] === '09171234567'
            && $request['sendername'] === 'LOBOCPARISH'
            && $request['message'] === 'Approved for Sunday. - Loboc Parish');
    }

    public function test_sms_iprog_driver_posts_to_iprog_with_an_international_number(): void
    {
        config([
            'services.sms.driver' => 'iprog',
            'services.iprog.api_token' => 'ip_token',
            'services.iprog.url' => 'https://www.iprogsms.com/api/v1/sms_messages',
        ]);
        Http::fake(['www.iprogsms.com/*' => Http::response(['status' => 200, 'message_id' => 'iSms-1'])]);
        Log::spy();
        $user = User::factory()->create(['mobile' => '9171234567']);

        (new SmsChannel)->send($user, new ParishUpdate('test', 'Subject', ['Line'], 'Approved for Sunday.'));

        Http::assertSent(fn (HttpRequest $request) => $request->url() === 'https://www.iprogsms.com/api/v1/sms_messages'
            && $request['api_token'] === 'ip_token'
            && $request['phone_number'] === '639171234567'
            && $request['message'] === 'Approved for Sunday.');
        Log::shouldNotHaveReceived('error');
    }

    public function test_iprog_error_response_is_logged_without_breaking_the_request(): void
    {
        config(['services.sms.driver' => 'iprog', 'services.iprog.api_token' => 'bad']);
        Http::fake(['*' => Http::response(['status' => 500, 'message' => 'Invalid Token'])]);
        Log::spy();
        $user = User::factory()->create(['mobile' => '9171234567']);

        (new SmsChannel)->send($user, new ParishUpdate('test', 'Subject', ['Line'], 'Hi'));

        Log::shouldHaveReceived('error')->once();
    }

    public function test_booking_confirmation_sms_fits_the_free_100_character_limit(): void
    {
        $user = User::factory()->create();
        $intention = MassIntention::factory()->create(['user_id' => $user->id]);

        $text = ParishUpdate::massIntentionSubmitted($intention)->toSms($user);

        $this->assertLessThanOrEqual(100, mb_strlen($text));
    }

    public function test_sms_failure_is_logged_without_breaking_the_request(): void
    {
        config(['services.sms.driver' => 'semaphore', 'services.semaphore.api_key' => 'k']);
        Http::fake(['*' => Http::response('Invalid API key', 401)]);
        Log::spy();
        $user = User::factory()->create(['mobile' => '9171234567']);

        (new SmsChannel)->send($user, new ParishUpdate('test', 'Subject', ['Line'], 'Hi'));

        Log::shouldHaveReceived('error')->once();
    }
}
