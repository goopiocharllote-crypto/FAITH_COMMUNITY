<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\MassIntention;
use App\Models\MassSchedule;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\GcashReceiptUploaded;
use App\Notifications\ParishUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * GCash QR payments: the parishioner scans the parish QR code, uploads the GCash receipt,
 * and the parish office checks it and confirms the payment.
 */
class OnlinePaymentTest extends TestCase
{
    use RefreshDatabase;

    /** A real 1x1 PNG, so the upload is detected as an image by its content. */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_gcash_donation_shows_the_qr_page_and_the_admin_confirms_the_uploaded_receipt(): void
    {
        $user = User::factory()->create(['mobile' => '9171234567']);
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

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
            ->assertSee('Type this amount in GCash')
            ->assertSee('data-copy-amount="250.00"', false)
            ->assertSee('type="file"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('Send receipt')
            ->assertSee(route('payments.donations.submit', $donation));

        $this->actingAs($user)
            ->post(route('payments.donations.submit', $donation), [
                'receipt' => $this->receipt(),
                'reference_number' => '1234 567 890 123',
            ])
            ->assertRedirect(route('donations.index'))
            ->assertSessionHas('status', fn (string $status) => str_contains($status, 'received your GCash receipt'));

        $payment = Payment::query()->sole();
        $this->assertSame('submitted', $payment->status);
        $this->assertSame('1234567890123', $payment->gateway_payment_id);
        $this->assertSame('gcash_qr', $payment->gateway);
        $this->assertSame('250.00', $payment->amount);
        $this->assertSame('image/png', $payment->receipt->mime);
        $this->assertSame(base64_decode(self::PNG), $payment->receipt->contents());
        $this->assertSame('Verifying', $donation->fresh()->payment_status);
        $this->assertSame('Pending', $donation->fresh()->status);
        Notification::assertSentTo($admin, GcashReceiptUploaded::class, fn (GcashReceiptUploaded $n) => str_contains($n->summary, $donation->paymentReference())
            && str_contains($n->details, '₱250.00'));

        $this->actingAs($user)
            ->get(route('donations.index'))
            ->assertSee('awaiting parish confirmation')
            ->assertDontSee('Pay ₱250.00 with GCash');

        $this->actingAs($admin)
            ->get(route('admin.donations'))
            ->assertSee('GCash ref. no.: 1234567890123')
            ->assertSee('amount to match ₱250.00')
            ->assertSee('GCash receipt submitted')
            ->assertSee('View GCash receipt')
            ->assertSee(route('payments.receipt', $payment));

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

    public function test_the_receipt_picture_is_required_and_must_be_a_small_image(): void
    {
        $user = User::factory()->create();
        $donation = Donation::factory()->create(['user_id' => $user->id, 'payment_method' => 'GCash', 'amount' => 100]);
        $url = route('payments.donations.pay', $donation);

        $this->actingAs($user)->from($url)
            ->post(route('payments.donations.submit', $donation), ['reference_number' => '1234567890123'])
            ->assertRedirect($url)
            ->assertSessionHasErrors('receipt');

        $this->actingAs($user)->from($url)
            ->post(route('payments.donations.submit', $donation), [
                'receipt' => UploadedFile::fake()->createWithContent('receipt.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n"),
            ])
            ->assertSessionHasErrors('receipt');

        $this->actingAs($user)->from($url)
            ->post(route('payments.donations.submit', $donation), [
                'receipt' => UploadedFile::fake()->createWithContent('huge.png', base64_decode(self::PNG).str_repeat('0', 3 * 1024 * 1024)),
            ])
            ->assertSessionHasErrors('receipt');

        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('payment_receipts', 0);
        $this->assertSame('Pending', $donation->fresh()->payment_status);
        Notification::assertNothingSentTo(User::query()->get());
    }

    public function test_reference_number_is_optional_but_must_have_13_digits_when_given(): void
    {
        $user = User::factory()->create();
        $donation = Donation::factory()->create(['user_id' => $user->id, 'payment_method' => 'GCash', 'amount' => 100]);
        $url = route('payments.donations.pay', $donation);

        foreach (['12345', '12345678901234'] as $bad) {
            $this->actingAs($user)->from($url)
                ->post(route('payments.donations.submit', $donation), ['receipt' => $this->receipt(), 'reference_number' => $bad])
                ->assertRedirect($url)
                ->assertSessionHasErrors('reference_number');
        }

        $this->assertDatabaseCount('payments', 0);

        $this->actingAs($user)
            ->post(route('payments.donations.submit', $donation), ['receipt' => $this->receipt()])
            ->assertSessionHasNoErrors();

        $this->assertNull(Payment::query()->sole()->gateway_payment_id);
        $this->assertSame('Verifying', $donation->fresh()->payment_status);
    }

    public function test_a_reference_number_cannot_be_reused_for_another_payment(): void
    {
        $user = User::factory()->create();
        $first = Donation::factory()->create(['user_id' => $user->id, 'payment_method' => 'GCash', 'amount' => 100]);
        $second = Donation::factory()->create(['user_id' => $user->id, 'payment_method' => 'GCash', 'amount' => 100]);

        $this->actingAs($user)
            ->post(route('payments.donations.submit', $first), ['receipt' => $this->receipt(), 'reference_number' => '1111222233334']);

        $this->actingAs($user)
            ->from(route('payments.donations.pay', $second))
            ->post(route('payments.donations.submit', $second), ['receipt' => $this->receipt(), 'reference_number' => '1111222233334'])
            ->assertSessionHasErrors('reference_number');

        $this->assertDatabaseCount('payments', 1);
        $this->assertSame('Pending', $second->fresh()->payment_status);
    }

    public function test_a_wrong_receipt_can_be_replaced_and_only_the_latest_counts(): void
    {
        $user = User::factory()->create();
        $donation = Donation::factory()->create(['user_id' => $user->id, 'payment_method' => 'GCash', 'amount' => 100]);

        $this->actingAs($user)->post(route('payments.donations.submit', $donation), ['receipt' => $this->receipt(), 'reference_number' => '1111222233334']);
        $this->actingAs($user)->post(route('payments.donations.submit', $donation), ['receipt' => $this->receipt(), 'reference_number' => '5555666677778']);

        $this->assertSame(1, Payment::query()->where('status', 'submitted')->count());
        $this->assertSame('5555666677778', Payment::query()->where('status', 'submitted')->sole()->gateway_payment_id);
        $this->assertSame(1, Payment::query()->where('status', 'cancelled')->count());

        $latest = Payment::query()->where('status', 'submitted')->sole();

        $this->actingAs($user)
            ->get(route('payments.donations.pay', $donation))
            ->assertSee('Receipt sent')
            ->assertSee(route('payments.receipt', $latest));
    }

    public function test_only_the_owner_and_administrators_can_open_a_receipt_picture(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
        $donation = Donation::factory()->create(['user_id' => $owner->id, 'payment_method' => 'GCash', 'amount' => 100]);

        $this->actingAs($owner)->post(route('payments.donations.submit', $donation), ['receipt' => $this->receipt()]);
        $payment = Payment::query()->sole();

        $this->actingAs($owner)
            ->get(route('payments.receipt', $payment))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->actingAs($admin)->get(route('payments.receipt', $payment))->assertOk();
        $this->actingAs($other)->get(route('payments.receipt', $payment))->assertForbidden();

        auth()->logout();
        $this->get(route('payments.receipt', $payment))->assertRedirect(route('login'));

        $noReceipt = $donation->payments()->create([
            'user_id' => $owner->id,
            'amount' => 100,
            'method' => 'GCash',
            'gateway' => 'gcash_qr',
            'status' => 'cancelled',
        ]);
        $this->actingAs($admin)->get(route('payments.receipt', $noReceipt))->assertNotFound();
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
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
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
            ->post(route('payments.mass-intentions.submit', $intention), ['receipt' => $this->receipt(), 'reference_number' => '9988776655443'])
            ->assertRedirect(route('mass-intentions.confirmation', $intention));

        $payment = Payment::query()->sole();
        $this->assertSame('Verifying', $intention->fresh()->payment_status);
        // Paying does not approve the intention; the parish office still reviews it.
        $this->assertSame('Pending', $intention->fresh()->status);
        Notification::assertSentTo($admin, GcashReceiptUploaded::class, fn (GcashReceiptUploaded $n) => str_contains($n->summary, $intention->referenceNumber())
            && str_contains($n->url, 'admin/mass-intentions'));

        $this->actingAs($user)
            ->get(route('mass-intentions.index'))
            ->assertSee('awaiting parish confirmation');

        $this->actingAs($admin)
            ->get(route('admin.mass-intentions'))
            ->assertSee('GCash reference no.:')
            ->assertSee('9988776655443')
            ->assertSee('Amount to match:')
            ->assertSee('₱150.00')
            ->assertSee('View GCash receipt')
            ->assertSee(route('payments.receipt', $payment))
            ->assertSee('Confirm GCash payment')
            ->assertSee(route('admin.mass-intentions.offering-received', $intention));

        $this->actingAs($admin)
            ->patch(route('admin.mass-intentions.offering-received', $intention))
            ->assertSessionHas('mass_success');

        $this->assertSame('Paid', $intention->fresh()->payment_status);
        $this->assertSame('paid', $payment->fresh()->status);
        Notification::assertSentTo($user, ParishUpdate::class, fn (ParishUpdate $n) => $n->kind === 'mass_intention.offering_received');

        $this->actingAs($user)
            ->get(route('mass-intentions.index'))
            ->assertSee('Paid · GCash');
    }

    public function test_admin_cannot_confirm_a_gcash_offering_before_a_receipt_is_submitted(): void
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
            ->post(route('payments.donations.submit', $donation), ['receipt' => $this->receipt()])
            ->assertForbidden();

        $this->assertDatabaseCount('payments', 0);

        auth()->logout();
        $this->post(route('payments.donations.submit', $donation), ['receipt' => $this->receipt()])
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

    public function test_the_qr_image_is_found_even_when_saved_with_a_different_extension(): void
    {
        // images/logo.png exists; asking for logo.jpg should fall back to it.
        config(['payments.gcash.qr_image' => 'images/logo.jpg']);

        $this->assertStringEndsWith('/images/logo.png', (string) \App\Payments\GcashPayments::qrImageUrl());
        $this->assertNotNull(\App\Payments\GcashPayments::qrImageUrl());

        // The real parish QR is committed as gcash-qr.jpeg and the default setting asks for gcash-qr.png.
        config(['payments.gcash.qr_image' => 'images/gcash-qr.png']);
        $this->assertNotNull(\App\Payments\GcashPayments::qrImageUrl());
    }

    public function test_paymongo_is_gone(): void
    {
        $this->assertFalse(class_exists(\App\Payments\Gateways\PayMongoGateway::class));
        $this->assertNull(config('payments.paymongo'));
        $this->post('/webhooks/paymongo', [])->assertNotFound();
    }

    private function receipt(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('gcash-receipt.png', base64_decode(self::PNG));
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
