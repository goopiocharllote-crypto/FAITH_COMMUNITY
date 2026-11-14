<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\MassIntention;
use App\Models\MassSchedule;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\ParishUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * GCash QR payments: the parishioner scans the parish QR code, enters the GCash reference
 * number, and the parish office confirms the payment.
 */
class OnlinePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_gcash_donation_shows_the_qr_page_and_waits_for_the_parish_to_confirm(): void
    {
        $user = User::factory()->create(['mobile' => '9171234567']);

        $this->actingAs($user)
            ->post(route('donations.store'), [
                'amount' => '250.00',
                'payment_method' => 'GCash',
                'message' => 'For the feast day',
            ])
            ->assertRedirect(route('payments.donations.pay', Donation::query()->sole()));

        $donation = Donation::query()->sole();
        $this->assertSame(0, Payment::query()->count());
        Notification::assertSentTo($user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'donation.submitted');

        $this->actingAs($user)
            ->get(route('payments.donations.pay', $donation))
            ->assertOk()
            ->assertSee('₱250.00')
            ->assertSee($donation->paymentReference())
            ->assertSee('Scan the parish QR code below')
            ->assertSee('13-digit reference number')
            ->assertSee(route('payments.donations.submit', $donation));

        $this->actingAs($user)
            ->post(route('payments.donations.submit', $donation), ['reference_number' => '1234 567 890 123'])
            ->assertRedirect(route('donations.index'))
            ->assertSessionHas('status', fn (string $status) => str_contains($status, '1234567890123'));

        $payment = Payment::query()->sole();
        $this->assertSame('submitted', $payment->status);
        $this->assertSame('1234567890123', $payment->gateway_payment_id);
        $this->assertSame('gcash_qr', $payment->gateway);
        $this->assertSame('250.00', $payment->amount);
        $this->assertSame('Verifying', $donation->fresh()->payment_status);
        $this->assertSame('Pending', $donation->fresh()->status);

        $this->actingAs($user)
            ->get(route('donations.index'))
            ->assertSee('awaiting parish confirmation')
            ->assertDontSee('Pay ₱250.00 with GCash');

        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

        $this->actingAs($admin)
            ->get(route('admin.donations'))
            ->assertSee('GCash ref. no.: 1234567890123')
            ->assertSee('GCash reference submitted');

        $this->actingAs($admin)
            ->patch(route('admin.donations.approve', $donation))
            ->assertSessionHas('status');

        $this->assertSame('Paid', $donation->fresh()->payment_status);
        $this->assertSame('Approved', $donation->fresh()->status);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
        Notification::assertSentTo($user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'donation.approved');

        $this->actingAs($user)
            ->get(route('donations.index'))
            ->assertSee('Paid · GCash');
    }

    public function test_reference_number_must_have_13_digits(): void
    {
        $user = User::factory()->create();
        $donation = Donation::factory()->create(['user_id' => $user->id, 'payment_method' => 'GCash', 'amount' => 100]);

        foreach (['', 'abc', '12345', '12345678901234'] as $bad) {
            $this->actingAs($user)
                ->from(route('payments.donations.pay', $donation))
                ->post(route('payments.donations.submit', $donation), ['reference_number' => $bad])
                ->assertRedirect(route('payments.donations.pay', $donation))
                ->assertSessionHasErrors('reference_number');
        }

        $this->assertDatabaseCount('payments', 0);
        $this->assertSame('Pending', $donation->fresh()->payment_status);
    }

    public function test_a_reference_number_cannot_be_reused_for_another_payment(): void
    {
        $user = User::factory()->create();
        $first = Donation::factory()->create(['user_id' => $user->id, 'payment_method' => 'GCash', 'amount' => 100]);
        $second = Donation::factory()->create(['user_id' => $user->id, 'payment_method' => 'GCash', 'amount' => 100]);

        $this->actingAs($user)
            ->post(route('payments.donations.submit', $first), ['reference_number' => '1111222233334']);

        $this->actingAs($user)
            ->from(route('payments.donations.pay', $second))
            ->post(route('payments.donations.submit', $second), ['reference_number' => '1111222233334'])
            ->assertSessionHasErrors('reference_number');

        $this->assertDatabaseCount('payments', 1);
        $this->assertSame('Pending', $second->fresh()->payment_status);
    }

    public function test_a_wrong_reference_can_be_corrected_and_only_the_latest_counts(): void
    {
        $user = User::factory()->create();
        $donation = Donation::factory()->create(['user_id' => $user->id, 'payment_method' => 'GCash', 'amount' => 100]);

        $this->actingAs($user)->post(route('payments.donations.submit', $donation), ['reference_number' => '1111222233334']);
        $this->actingAs($user)->post(route('payments.donations.submit', $donation), ['reference_number' => '5555666677778']);

        $this->assertSame(1, Payment::query()->where('status', 'submitted')->count());
        $this->assertSame('5555666677778', Payment::query()->where('status', 'submitted')->sole()->gateway_payment_id);
        $this->assertSame(1, Payment::query()->where('status', 'cancelled')->count());

        $this->actingAs($user)
            ->get(route('payments.donations.pay', $donation))
            ->assertSee('Reference number submitted: 5555666677778');
    }

    public function test_pay_at_parish_donations_have_no_qr_payment(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('donations.store'), ['amount' => '10', 'payment_method' => 'Cash'])
            ->assertRedirect(route('donations.index'));

        $donation = Donation::query()->sole();
        $this->assertDatabaseCount('payments', 0);

        $this->actingAs($user)
            ->get(route('payments.donations.pay', $donation))
            ->assertRedirect(route('donations.index'))
            ->assertSessionHasErrors('payment');
    }

    public function test_small_gcash_amounts_are_allowed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('donations.store'), ['amount' => '5', 'payment_method' => 'GCash'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('donations', 1);
    }

    public function test_mass_intention_offering_is_paid_with_the_qr_code_and_confirmed_by_the_admin(): void
    {
        $user = User::factory()->create();
        $schedule = $this->openSchedule();

        $this->actingAs($user)->post(route('mass-intentions.store'), [
            'intention_type' => 'Healing',
            'intention_for' => 'Lola Rosa',
            'schedule_id' => $schedule->id,
            'mobile' => '9171234567',
            'offering_amount' => '150.00',
            'payment_method' => 'GCash',
        ])->assertRedirect(route('payments.mass-intentions.pay', MassIntention::query()->sole()));

        $intention = MassIntention::query()->sole();
        $this->assertSame('GCash', $intention->payment_method);
        $this->assertSame('Pending', $intention->payment_status);

        $this->actingAs($user)
            ->get(route('payments.mass-intentions.pay', $intention))
            ->assertOk()
            ->assertSee('₱150.00')
            ->assertSee($intention->referenceNumber());

        $this->actingAs($user)
            ->post(route('payments.mass-intentions.submit', $intention), ['reference_number' => '9988776655443'])
            ->assertRedirect(route('mass-intentions.confirmation', $intention));

        $this->assertSame('Verifying', $intention->fresh()->payment_status);
        // Paying does not approve the intention; the parish office still reviews it.
        $this->assertSame('Pending', $intention->fresh()->status);

        $this->actingAs($user)
            ->get(route('mass-intentions.index'))
            ->assertSee('awaiting parish confirmation');

        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

        $this->actingAs($admin)
            ->get(route('admin.mass-intentions'))
            ->assertSee('GCash reference no.:')
            ->assertSee('9988776655443')
            ->assertSee('Confirm GCash payment')
            ->assertSee(route('admin.mass-intentions.offering-received', $intention));

        $this->actingAs($admin)
            ->patch(route('admin.mass-intentions.offering-received', $intention))
            ->assertSessionHas('mass_success');

        $this->assertSame('Paid', $intention->fresh()->payment_status);
        $this->assertSame('paid', Payment::query()->sole()->status);
        Notification::assertSentTo($user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'mass_intention.offering_received');

        $this->actingAs($user)
            ->get(route('mass-intentions.index'))
            ->assertSee('Paid · GCash');
    }

    public function test_admin_cannot_confirm_a_gcash_offering_before_a_reference_is_submitted(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
        $intention = MassIntention::factory()->create(['payment_method' => 'GCash', 'offering_amount' => 200]);

        $this->actingAs($admin)
            ->get(route('admin.mass-intentions'))
            ->assertDontSee('Confirm GCash payment');

        $this->actingAs($admin)
            ->patch(route('admin.mass-intentions.offering-received', $intention))
            ->assertSessionHasErrors('mass_intention');

        $this->assertSame('Pending', $intention->fresh()->payment_status);
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
        $donation = Donation::factory()->create(['user_id' => $owner->id, 'payment_method' => 'GCash', 'amount' => 100]);

        $this->actingAs($other)->get(route('payments.donations.pay', $donation))->assertForbidden();
        $this->actingAs($other)
            ->post(route('payments.donations.submit', $donation), ['reference_number' => '1234567890123'])
            ->assertForbidden();

        $this->assertDatabaseCount('payments', 0);

        auth()->logout();
        $this->post(route('payments.donations.submit', $donation), ['reference_number' => '1234567890123'])
            ->assertRedirect(route('login'));
    }

    public function test_the_qr_image_is_shown_when_it_exists_and_a_notice_when_it_does_not(): void
    {
        $user = User::factory()->create();
        $donation = Donation::factory()->create(['user_id' => $user->id, 'payment_method' => 'GCash', 'amount' => 100]);

        config(['payments.gcash.qr_image' => 'images/missing-qr-file.png']);
        $this->actingAs($user)
            ->get(route('payments.donations.pay', $donation))
            ->assertSee('The GCash QR code is not available yet.')
            ->assertDontSee('alt="Parish GCash QR code"', false);

        config(['payments.gcash.qr_image' => 'images/logo.png', 'payments.gcash.account_name' => 'Loboc Parish']);
        $this->actingAs($user)
            ->get(route('payments.donations.pay', $donation))
            ->assertSee('alt="Parish GCash QR code"', false)
            ->assertSee('Loboc Parish')
            ->assertDontSee('The GCash QR code is not available yet.');
    }

    public function test_paymongo_is_gone(): void
    {
        $this->assertFalse(class_exists(\App\Payments\Gateways\PayMongoGateway::class));
        $this->assertNull(config('payments.paymongo'));
        $this->post('/webhooks/paymongo', [])->assertNotFound();
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
