<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\MassIntention;
use App\Models\MassSchedule;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\ParishUpdate;
use App\Payments\Gateways\PayMongoGateway;
use App\Payments\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OnlinePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['payments.gateway' => 'simulator']);
        Notification::fake();
    }

    public function test_gcash_donation_goes_through_checkout_and_is_marked_received_when_paid(): void
    {
        $user = User::factory()->create(['mobile' => '9171234567']);

        $response = $this->actingAs($user)->post(route('donations.store'), [
            'amount' => '250.00',
            'payment_method' => 'GCash',
            'message' => 'For the feast day',
        ]);

        $donation = Donation::query()->sole();
        $payment = Payment::query()->sole();

        $response->assertRedirect(route('payments.simulator.show', $payment));
        $this->assertSame('pending', $payment->status);
        $this->assertSame('250.00', $payment->amount);
        $this->assertTrue($payment->payable->is($donation));
        Notification::assertSentTo($user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'donation.submitted');

        $this->actingAs($user)
            ->get(route('payments.simulator.show', $payment))
            ->assertOk()
            ->assertSee('Test mode')
            ->assertSee('₱250.00')
            ->assertSee($donation->paymentReference());

        $this->actingAs($user)
            ->post(route('payments.simulator.complete', $payment), ['decision' => 'pay'])
            ->assertRedirect(route('payments.return', ['payment' => $payment, 'result' => 'success']));

        $this->actingAs($user)
            ->get(route('payments.return', ['payment' => $payment, 'result' => 'success']))
            ->assertRedirect(route('donations.index'))
            ->assertSessionHas('status', fn (string $status) => str_contains($status, 'GCash payment of ₱250.00 received'));

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
        $this->assertSame('Paid', $donation->fresh()->payment_status);
        $this->assertSame('Approved', $donation->fresh()->status);
        Notification::assertSentToTimes($user, ParishUpdate::class, 2);
        Notification::assertSentTo($user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'payment.received');

        $this->actingAs($user)
            ->get(route('donations.index'))
            ->assertSee('Paid · GCash')
            ->assertDontSee('Pay ₱250.00 with GCash');
    }

    public function test_cancelled_gcash_checkout_keeps_the_donation_and_it_can_be_paid_later(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('donations.store'), ['amount' => '100', 'payment_method' => 'GCash']);
        $first = Payment::query()->sole();

        $this->actingAs($user)->post(route('payments.simulator.complete', $first), ['decision' => 'cancel']);
        $this->actingAs($user)
            ->get(route('payments.return', ['payment' => $first, 'result' => 'cancel']))
            ->assertRedirect(route('donations.index'))
            ->assertSessionHasErrors('payment');

        $donation = Donation::query()->sole();
        $this->assertSame('cancelled', $first->fresh()->status);
        $this->assertSame('Pending', $donation->payment_status);

        $this->actingAs($user)
            ->get(route('donations.index'))
            ->assertSee('Pay ₱100.00 with GCash')
            ->assertSee(route('payments.donations.pay', $donation));

        $this->actingAs($user)
            ->post(route('payments.donations.pay', $donation))
            ->assertRedirect();

        $this->assertSame(2, Payment::query()->count());
        $this->assertSame('pending', Payment::query()->latest('id')->first()->status);
    }

    public function test_starting_a_new_checkout_supersedes_an_abandoned_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('donations.store'), ['amount' => '100', 'payment_method' => 'GCash']);
        $donation = Donation::query()->sole();
        $abandoned = Payment::query()->sole();

        $this->actingAs($user)->post(route('payments.donations.pay', $donation));

        $this->assertSame('cancelled', $abandoned->fresh()->status);
        $this->assertSame(1, Payment::query()->where('status', 'pending')->count());
    }

    public function test_gcash_amount_must_meet_the_minimum_and_parish_option_has_no_checkout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('donations.index'))
            ->post(route('donations.store'), ['amount' => '10', 'payment_method' => 'GCash'])
            ->assertRedirect(route('donations.index'))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('donations', 0);

        $this->actingAs($user)
            ->post(route('donations.store'), ['amount' => '10', 'payment_method' => 'Cash'])
            ->assertRedirect(route('donations.index'));

        $this->assertDatabaseCount('donations', 1);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_mass_intention_offering_can_be_paid_through_gcash(): void
    {
        $user = User::factory()->create();
        $schedule = $this->openSchedule();

        $response = $this->actingAs($user)->post(route('mass-intentions.store'), [
            'intention_type' => 'Healing',
            'intention_for' => 'Lola Rosa',
            'schedule_id' => $schedule->id,
            'mobile' => '9171234567',
            'offering_amount' => '150.00',
            'payment_method' => 'GCash',
        ]);

        $intention = MassIntention::query()->sole();
        $payment = Payment::query()->sole();
        $response->assertRedirect(route('payments.simulator.show', $payment));
        $this->assertSame('GCash', $intention->payment_method);

        $this->actingAs($user)->post(route('payments.simulator.complete', $payment), ['decision' => 'pay']);
        $this->actingAs($user)
            ->get(route('payments.return', ['payment' => $payment, 'result' => 'success']))
            ->assertRedirect(route('mass-intentions.index'));

        $this->assertSame('Paid', $intention->fresh()->payment_status);
        // Paying does not approve the intention; the parish office still reviews it.
        $this->assertSame('Pending', $intention->fresh()->status);

        $this->actingAs($user)
            ->get(route('mass-intentions.index'))
            ->assertSee('Paid · GCash');
    }

    public function test_gcash_offering_below_minimum_is_rejected(): void
    {
        $user = User::factory()->create();
        $schedule = $this->openSchedule();

        $this->actingAs($user)
            ->from(route('mass-intentions.index'))
            ->post(route('mass-intentions.store'), [
                'intention_type' => 'Souls',
                'intention_for' => 'Lolo Ben',
                'schedule_id' => $schedule->id,
                'mobile' => '9171234567',
                'offering_amount' => '5',
                'payment_method' => 'GCash',
            ])
            ->assertSessionHasErrors('offering_amount');

        $this->assertDatabaseCount('mass_intentions', 0);
    }

    public function test_admin_can_mark_a_pay_at_parish_offering_received(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
        $intention = MassIntention::factory()->create([
            'payment_method' => 'Cash',
            'offering_amount' => 200,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.mass-intentions'))
            ->assertSee('Mark offering received')
            ->assertSee(route('admin.mass-intentions.offering-received', $intention));

        $this->actingAs($admin)
            ->patch(route('admin.mass-intentions.offering-received', $intention))
            ->assertSessionHas('mass_success', 'Offering marked as received.');

        $this->assertSame('Paid', $intention->fresh()->payment_status);
        Notification::assertSentTo($intention->user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'mass_intention.offering_received');

        $this->actingAs($admin)
            ->patch(route('admin.mass-intentions.offering-received', $intention))
            ->assertSessionHasErrors('mass_intention');
    }

    public function test_parishioners_cannot_touch_someone_elses_payments(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($owner)->post(route('donations.store'), ['amount' => '100', 'payment_method' => 'GCash']);
        $donation = Donation::query()->sole();
        $payment = Payment::query()->sole();

        $this->actingAs($other)->post(route('payments.donations.pay', $donation))->assertForbidden();
        $this->actingAs($other)->get(route('payments.simulator.show', $payment))->assertForbidden();
        $this->actingAs($other)->post(route('payments.simulator.complete', $payment), ['decision' => 'pay'])->assertForbidden();
        $this->actingAs($other)->get(route('payments.return', $payment))->assertForbidden();

        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_simulator_is_unavailable_when_paymongo_is_the_gateway(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('donations.store'), ['amount' => '100', 'payment_method' => 'GCash']);
        $payment = Payment::query()->sole();

        config(['payments.gateway' => 'paymongo']);

        $this->actingAs($user)->get(route('payments.simulator.show', $payment))->assertNotFound();
    }

    // ------------------------------------------------------------------
    // PayMongo
    // ------------------------------------------------------------------

    public function test_paymongo_checkout_is_created_for_gcash_and_confirmed_on_return(): void
    {
        $this->usePayMongo();
        Http::fake([
            'api.paymongo.com/v1/checkout_sessions' => Http::response([
                'data' => [
                    'id' => 'cs_test_123',
                    'attributes' => ['checkout_url' => 'https://checkout.paymongo.com/cs_test_123'],
                ],
            ]),
            'api.paymongo.com/v1/checkout_sessions/cs_test_123' => Http::response([
                'data' => [
                    'id' => 'cs_test_123',
                    'attributes' => [
                        'status' => 'active',
                        'payments' => [['id' => 'pay_abc', 'attributes' => ['status' => 'paid']]],
                    ],
                ],
            ]),
        ]);

        $user = User::factory()->create(['mobile' => '9171234567']);

        $this->actingAs($user)
            ->post(route('donations.store'), ['amount' => '500.50', 'payment_method' => 'GCash'])
            ->assertRedirect('https://checkout.paymongo.com/cs_test_123');

        Http::assertSent(function (HttpRequest $request) {
            $attributes = $request['data']['attributes'] ?? [];

            return $request->method() === 'POST'
                && $request->url() === 'https://api.paymongo.com/v1/checkout_sessions'
                && $request->hasHeader('Authorization', 'Basic '.base64_encode('sk_test_secret:'))
                && $attributes['payment_method_types'] === ['gcash']
                && $attributes['line_items'][0]['amount'] === 50050
                && $attributes['line_items'][0]['currency'] === 'PHP'
                && $attributes['billing']['phone'] === '09171234567'
                && str_contains($attributes['success_url'], '/return?result=success');
        });

        $payment = Payment::query()->sole();
        $this->assertSame('paymongo', $payment->gateway);
        $this->assertSame('cs_test_123', $payment->checkout_id);

        $this->actingAs($user)
            ->get(route('payments.return', ['payment' => $payment, 'result' => 'success']))
            ->assertRedirect(route('donations.index'));

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('pay_abc', $payment->fresh()->gateway_payment_id);
        $this->assertSame('Approved', Donation::query()->sole()->status);
    }

    public function test_paymongo_outage_keeps_the_record_and_shows_a_retry_message(): void
    {
        $this->usePayMongo();
        Http::fake(['api.paymongo.com/*' => Http::response(['errors' => [['detail' => 'Service unavailable']]], 503)]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('donations.store'), ['amount' => '100', 'payment_method' => 'GCash'])
            ->assertRedirect(route('donations.index'))
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('donations', 1);
        $this->assertSame('failed', Payment::query()->sole()->status);
    }

    public function test_paymongo_webhook_marks_payment_paid_once_and_rejects_bad_signatures(): void
    {
        $this->usePayMongo();
        Http::fake([
            'api.paymongo.com/v1/checkout_sessions' => Http::response([
                'data' => ['id' => 'cs_test_hook', 'attributes' => ['checkout_url' => 'https://checkout.paymongo.com/x']],
            ]),
        ]);

        $user = User::factory()->create();
        $this->actingAs($user)->post(route('donations.store'), ['amount' => '300', 'payment_method' => 'GCash']);
        $payment = Payment::query()->sole();

        $body = json_encode(['data' => ['attributes' => [
            'type' => 'checkout_session.payment.paid',
            'data' => [
                'id' => 'cs_test_hook',
                'attributes' => ['payments' => [['id' => 'pay_hook', 'attributes' => ['status' => 'paid']]]],
            ],
        ]]]);

        $this->call('POST', route('payments.webhook'), [], [], [], [
            'HTTP_PAYMONGO_SIGNATURE' => 't='.time().',te=wrong,li=',
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertUnauthorized();

        $this->assertSame('pending', $payment->fresh()->status);

        $timestamp = time();
        $signature = 't='.$timestamp.',te='.hash_hmac('sha256', $timestamp.'.'.$body, 'whsk_test').',li=';

        foreach ([1, 2] as $delivery) {
            $this->call('POST', route('payments.webhook'), [], [], [], [
                'HTTP_PAYMONGO_SIGNATURE' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ], $body)->assertOk();
        }

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('pay_hook', $payment->fresh()->gateway_payment_id);
        Notification::assertSentToTimes($user, ParishUpdate::class, 2); // donation.submitted + one payment.received
    }

    public function test_paymongo_signature_rejects_stale_timestamps(): void
    {
        $old = time() - 3600;
        $header = 't='.$old.',te='.hash_hmac('sha256', $old.'.{}', 'secret');

        $this->assertFalse(PayMongoGateway::signatureIsValid($header, '{}', 'secret'));
        $this->assertFalse(PayMongoGateway::signatureIsValid('t=1,te=abc', '{}', null));
    }

    private function usePayMongo(): void
    {
        config([
            'payments.gateway' => 'paymongo',
            'payments.paymongo.secret_key' => 'sk_test_secret',
            'payments.paymongo.webhook_secret' => 'whsk_test',
            'payments.paymongo.base_url' => 'https://api.paymongo.com/v1',
        ]);
        $this->app->forgetInstance(PaymentGateway::class);
    }

    private function openSchedule(): MassSchedule
    {
        return MassSchedule::query()->create([
            'title' => 'Sunday Mass',
            'mass_date' => now()->addDays(3)->setTime(8, 0),
            'closing_at' => now()->addDays(2),
            'is_active' => true,
        ]);
    }
}
