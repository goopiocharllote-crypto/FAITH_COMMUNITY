<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\MassIntention;
use App\Models\PrayerRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_center_connects_the_user_owned_request_types(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $massIntention = MassIntention::factory()->create([
            'user_id' => $user->id,
            'intention_for' => 'My family',
        ]);
        PrayerRequest::create([
            'user_id' => $user->id,
            'title' => 'For healing',
            'request' => 'Please pray for my family.',
        ]);
        Donation::create([
            'user_id' => $user->id,
            'amount' => 250,
            'payment_method' => 'Cash',
        ]);
        MassIntention::factory()->create([
            'user_id' => $otherUser->id,
            'intention_for' => 'Another family',
        ]);

        $this->actingAs($user)
            ->get(route('users.my-request'))
            ->assertOk()
            ->assertSee('My family')
            ->assertSee('For healing')
            ->assertSee('Please pray for my family.')
            ->assertSee('₱250.00')
            ->assertDontSee('Another family')
            ->assertSee(route('mass-intentions.index'))
            ->assertSee(route('users.prayer-requests.store'))
            ->assertSee(route('donations.store'));

        $this->assertSame($massIntention->user_id, $user->id);
    }

    public function test_user_can_submit_prayer_request_and_validation_rejects_invalid_input(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('users.prayer-requests.store'), [
                'title' => 'For my family',
                'request' => 'Please pray for good health.',
            ])
            ->assertRedirect(route('users.my-request'))
            ->assertSessionHas('status', 'Your prayer request has been submitted for review.');

        $this->assertDatabaseHas('prayer_requests', [
            'user_id' => $user->id,
            'title' => 'For my family',
            'request' => 'Please pray for good health.',
            'status' => 'Pending',
        ]);

        $this->from(route('users.my-request'))
            ->post(route('users.prayer-requests.store'), [
                'title' => str_repeat('a', 121),
                'request' => '',
            ])
            ->assertRedirect(route('users.my-request'))
            ->assertSessionHasErrors(['title', 'request']);

        $this->assertDatabaseCount('prayer_requests', 1);
    }

    public function test_virtual_candle_prayers_are_saved_and_status_is_visible_in_my_requests(): void
    {
        $user = User::factory()->create();
        $prayer = 'Please bless and guide my family.';

        $this->actingAs($user)
            ->get(route('virtual-candle'))
            ->assertOk()
            ->assertSee(route('virtual-candle.store'))
            ->assertSee(route('users.my-request'));

        $this->post(route('virtual-candle.store'), [
            'intention_type' => 'Healing',
            'prayer' => $prayer,
        ])
            ->assertRedirect(route('users.my-request'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('prayer_requests', [
            'user_id' => $user->id,
            'title' => 'Virtual Candle - Healing',
            'request' => $prayer,
            'status' => 'Pending',
        ]);

        $this->get(route('users.my-request'))
            ->assertOk()
            ->assertSee('Virtual Candle - Healing')
            ->assertSee($prayer)
            ->assertSee('Pending');
    }

    public function test_suggested_prayer_stays_hidden_until_an_intention_type_is_chosen(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('virtual-candle'))
            ->assertOk()
            ->assertSee('Choose intention type')
            ->assertSee('id="prayerSection" class="hidden"', false)
            ->assertSee('id="typeSubmitError"', false)
            ->assertSee('Choose an intention type first.');

        // After a failed submit the chosen type comes back and its prayer section stays visible.
        $this->withSession(['_old_input' => ['intention_type' => 'Healing', 'prayer' => 'Heal them, Lord.']])
            ->actingAs($user)
            ->get(route('virtual-candle'))
            ->assertOk()
            ->assertSee('id="prayerSection" class=""', false)
            ->assertSee('Heal them, Lord.');
    }

    public function test_virtual_candle_prayer_validates_input_and_requires_parishioner_authentication(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('virtual-candle'))
            ->post(route('virtual-candle.store'), [
                'intention_type' => 'Not an intention',
                'prayer' => '',
            ])
            ->assertRedirect(route('virtual-candle'))
            ->assertSessionHasErrors(['intention_type', 'prayer']);

        $this->assertDatabaseCount('prayer_requests', 0);

        auth()->logout();

        $this->post(route('virtual-candle.store'), [
            'intention_type' => 'Healing',
            'prayer' => 'Please bless my family.',
        ])->assertRedirect(route('login'));
    }

    public function test_user_can_pledge_a_donation_to_give_at_the_parish(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('donations.store'), [
                'amount' => '125.50',
                'payment_method' => 'Cash',
                'message' => 'Parish support',
            ])
            ->assertRedirect(route('donations.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('donations', [
            'user_id' => $user->id,
            'amount' => '125.50',
            'payment_method' => 'Cash',
            'payment_status' => 'Pending',
            'message' => 'Parish support',
            'status' => 'Pending',
        ]);
        $this->assertDatabaseCount('payments', 0);

        $this->actingAs($user)
            ->get(route('donations.index'))
            ->assertOk()
            ->assertSee('Donate at the parish')
            ->assertSee('Pay at parish office');
    }

    public function test_donation_validation_rejects_invalid_amount_and_payment_method(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('donations.index'))
            ->post(route('donations.store'), [
                'amount' => '0.999',
                'payment_method' => 'Bitcoin',
                'reference_number' => str_repeat('x', 81),
            ])
            ->assertRedirect(route('donations.index'))
            ->assertSessionHasErrors(['amount', 'payment_method', 'reference_number']);

        $this->assertDatabaseCount('donations', 0);
    }

    public function test_admins_can_review_prayer_requests_and_donations(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);
        $user = User::factory()->create();
        $prayerRequest = PrayerRequest::create([
            'user_id' => $user->id,
            'title' => 'Prayer for healing',
            'request' => 'Please pray for healing.',
        ]);
        $donation = Donation::create([
            'user_id' => $user->id,
            'amount' => 400,
            'payment_method' => 'Cash',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.prayer-requests'))
            ->assertOk()
            ->assertSee('Prayer for healing');
        $this->get(route('admin.donations'))
            ->assertOk()
            ->assertSee('₱400.00');

        $this->patch(route('admin.prayer-requests.approve', $prayerRequest))
            ->assertRedirect()
            ->assertSessionHas('status', 'Prayer request approved.');
        $this->patch(route('admin.donations.approve', $donation))
            ->assertRedirect()
            ->assertSessionHas('status', 'Donation marked received after verification.');

        $this->assertDatabaseHas('prayer_requests', [
            'id' => $prayerRequest->id,
            'status' => 'Approved',
        ]);
        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => 'Approved',
        ]);
    }

    public function test_admin_can_mark_approved_mass_intention_read_at_church_and_user_sees_status(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);
        $user = User::factory()->create();
        $massIntention = MassIntention::factory()->create([
            'user_id' => $user->id,
            'status' => 'Approved',
            'church_read_at' => null,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.mass-intentions.read-in-church', $massIntention))
            ->assertRedirect()
            ->assertSessionHas('mass_success', 'Mass Intention marked as read in church.');

        $this->assertNotNull($massIntention->fresh()->church_read_at);

        $this->actingAs($user)
            ->get(route('users.my-request'))
            ->assertOk()
            ->assertSee('Read at the church')
            ->assertSee('bg-green-100', false);

        $this->actingAs($admin)
            ->delete(route('admin.mass-intentions.unread-in-church', $massIntention))
            ->assertRedirect()
            ->assertSessionHas('mass_success', 'Mass Intention marked as not read in church.');

        $this->assertNull($massIntention->fresh()->church_read_at);

        $this->actingAs($user)
            ->get(route('users.my-request'))
            ->assertOk()
            ->assertDontSee('Read at the church');
    }

    public function test_admin_mass_intentions_page_shows_request_details_and_action_routes(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
        $user = User::factory()->create();
        $massIntention = MassIntention::factory()->create([
            'user_id' => $user->id,
            'intention_for' => 'The Santos family',
            'additional_message' => 'For health and peace.',
            'mobile' => '9171234567',
            'status' => 'Pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.mass-intentions'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($user->email)
            ->assertSee('9171234567')
            ->assertSee('The Santos family')
            ->assertSee('For health and peace.')
            ->assertSee(route('admin.mass-intentions.approve', $massIntention))
            ->assertSee(route('admin.mass-intentions.reject', $massIntention));
    }

    public function test_admin_mass_intention_routes_update_status_and_report_invalid_transitions(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
        $pending = MassIntention::factory()->create(['status' => 'Pending']);
        $approved = MassIntention::factory()->create(['status' => 'Approved']);

        $this->actingAs($admin)
            ->patch(route('admin.mass-intentions.reject', $pending))
            ->assertRedirect()
            ->assertSessionHas('mass_success', 'Mass Intention rejected successfully.');

        $this->assertDatabaseHas('mass_intentions', [
            'id' => $pending->id,
            'status' => 'Rejected',
        ]);

        $this->patch(route('admin.mass-intentions.approve', $pending))
            ->assertRedirect()
            ->assertSessionHasErrors('mass_intention');

        $this->get(route('admin.mass-intentions'))
            ->assertOk()
            ->assertSee('This Mass Intention can no longer be updated.');

        $this->assertDatabaseHas('mass_intentions', [
            'id' => $approved->id,
            'status' => 'Approved',
        ]);
    }

    public function test_parishioner_cannot_access_mass_intention_admin_routes(): void
    {
        $user = User::factory()->create();
        $massIntention = MassIntention::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.mass-intentions'))
            ->assertForbidden();

        $this->patch(route('admin.mass-intentions.approve', $massIntention))
            ->assertForbidden();
    }

    public function test_mass_intention_cannot_be_marked_read_before_approval(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);
        $massIntention = MassIntention::factory()->create([
            'status' => 'Pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.mass-intentions.read-in-church', $massIntention))
            ->assertRedirect()
            ->assertSessionHasErrors('mass_intention');

        $this->assertNull($massIntention->fresh()->church_read_at);
    }

    public function test_admin_dashboard_includes_prayer_activity_and_verified_donations(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);
        $user = User::factory()->create();
        PrayerRequest::create([
            'user_id' => $user->id,
            'title' => 'For peace',
            'request' => 'Please pray for peace.',
        ]);
        Donation::create([
            'user_id' => $user->id,
            'amount' => 275,
            'payment_method' => 'Cash',
            'status' => 'Approved',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('pendingRequests', 1)
            ->assertViewHas('totalDonations', 275)
            ->assertSee('New Prayer Request: For peace')
            ->assertSee('Donation record submitted: ₱275.00');
    }

    public function test_parishioner_cannot_access_another_users_request_or_admin_reviews(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $prayerRequest = PrayerRequest::create([
            'user_id' => $otherUser->id,
            'title' => 'Private request',
            'request' => 'This belongs to another parishioner.',
        ]);
        $donation = Donation::create([
            'user_id' => $otherUser->id,
            'amount' => 100,
            'payment_method' => 'Cash',
        ]);

        $this->actingAs($user)
            ->get(route('users.my-request'))
            ->assertOk()
            ->assertDontSee('Private request');

        $this->patch(route('admin.prayer-requests.approve', $prayerRequest))
            ->assertForbidden();
        $this->patch(route('admin.donations.approve', $donation))
            ->assertForbidden();

        $this->assertDatabaseHas('prayer_requests', [
            'id' => $prayerRequest->id,
            'status' => 'Pending',
        ]);
        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => 'Pending',
        ]);
    }
}
