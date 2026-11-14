<?php

namespace Tests\Feature;

use App\Models\MassIntention;
use App\Models\MassSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MassIntentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_submit_a_mass_intention(): void
    {
        $user = User::factory()->create();
        $schedule = $this->createOpenSchedule();

        $this->actingAs($user)
            ->get(route('mass-intentions.index'))
            ->assertOk()
            ->assertSee('name="schedule_id"', false)
            ->assertSee($schedule->title)
            ->assertSee('Requests close:')
            ->assertSee($schedule->closing_at->format('M j, Y · g:i A'));

        $response = $this->actingAs($user)->post(route('mass-intentions.store'), [
            'intention_type' => 'Thanksgiving',
            'intention_for' => 'The Santos family',
            'additional_message' => 'For continued health and peace.',
            'schedule_id' => $schedule->id,
            'mobile' => '9171234567',
            'offering_amount' => '100.00',
        ]);

        $response->assertRedirect(route('mass-intentions.confirmation', MassIntention::query()->sole()))
            ->assertSessionHas('status', 'Your Mass Intention request has been submitted.');

        $this->assertDatabaseHas('mass_intentions', [
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'mass_date' => $schedule->mass_date->format('Y-m-d H:i:s'),
            'intention_type' => 'Thanksgiving',
            'intention_for' => 'The Santos family',
            'mobile' => '9171234567',
            'offering_amount' => '100.00',
            'status' => 'Pending',
            'payment_status' => 'Pending',
        ]);
    }

    public function test_mass_intention_date_time_uses_standard_time_controls(): void
    {
        $schedule = $this->createOpenSchedule();

        $this->actingAs(User::factory()->create())
            ->get(route('mass-intentions.index'))
            ->assertOk()
            ->assertSee('Select Date & Time', false)
            ->assertSee('id="mass-date-trigger"', false)
            ->assertSee('<dialog', false)
            ->assertSee('id="mass-date-dialog"', false)
            ->assertSee('Choose a Mass schedule', false)
            ->assertSee('aria-label="Mass date calendar"', false)
            ->assertSee('calendar-days', false)
            ->assertSee('calendar-previous-month', false)
            ->assertSee('calendar-next-month', false)
            ->assertSee('aria-label="Mass times and request closing"', false)
            ->assertSee('class="schedule-option', false)
            ->assertSee($schedule->title)
            ->assertSee($schedule->closing_at->format('M j, Y · g:i A'))
            ->assertSee('Open for requests')
            ->assertSee('class="confirm-date-picker', false)
            ->assertSee('class="cancel-date-picker', false);
    }

    public function test_closed_schedule_is_visible_but_cannot_receive_an_intention(): void
    {
        $user = User::factory()->create();
        $massDate = now()->addDays(2);
        $schedule = MassSchedule::query()->create([
            'title' => 'Closed Morning Mass',
            'mass_date' => $massDate,
            'closing_at' => now()->subMinute(),
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('mass-intentions.index'))
            ->assertOk()
            ->assertSee($schedule->title)
            ->assertSee('Requests closed')
            ->assertSee('disabled', false);

        $this->post(route('mass-intentions.store'), [
            'intention_type' => 'Thanksgiving',
            'intention_for' => 'My family',
            'schedule_id' => $schedule->id,
            'mobile' => '9171234567',
            'offering_amount' => '100.00',
        ])
            ->assertRedirect(route('mass-intentions.index'))
            ->assertSessionHasErrors('schedule_id');

        $this->assertDatabaseCount('mass_intentions', 0);
    }

    public function test_my_requests_shows_admin_schedules_and_only_open_schedules_can_be_chosen(): void
    {
        $user = User::factory()->create();
        $openSchedule = $this->createOpenSchedule();
        $closedSchedule = MassSchedule::query()->create([
            'title' => 'Closed Morning Mass',
            'mass_date' => now()->addDays(2),
            'closing_at' => now()->subMinute(),
            'is_active' => true,
        ]);
        MassSchedule::query()->create([
            'title' => 'Unpublished Mass',
            'mass_date' => now()->addDays(4),
            'closing_at' => now()->addDays(3),
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get(route('users.my-request'))
            ->assertOk()
            ->assertSee('Available Mass Schedules')
            ->assertSee($openSchedule->title)
            ->assertSee($openSchedule->mass_date->format('l, M j, Y · g:i A'))
            ->assertSee($openSchedule->closing_at->format('M j, Y · g:i A'))
            ->assertSee(route('mass-intentions.index', ['schedule_id' => $openSchedule->id]))
            ->assertSee($closedSchedule->title)
            ->assertSee('Requests closed')
            ->assertDontSee('Unpublished Mass')
            ->assertDontSee(route('mass-intentions.index', ['schedule_id' => $closedSchedule->id]));
    }

    public function test_intention_submission_cannot_override_the_schedule_mass_time(): void
    {
        $user = User::factory()->create();
        $schedule = $this->createOpenSchedule();
        $differentMassDate = now()->addDays(8);

        $this->actingAs($user)
            ->post(route('mass-intentions.store'), [
                'intention_type' => 'Thanksgiving',
                'intention_for' => 'My family',
                'schedule_id' => $schedule->id,
                'mass_date' => $differentMassDate->format('Y-m-d\TH:i'),
                'mobile' => '9171234567',
                'offering_amount' => '100.00',
            ])
            ->assertRedirect(route('mass-intentions.confirmation', MassIntention::query()->sole()))
            ->assertSessionHas('status', 'Your Mass Intention request has been submitted.');

        $this->assertDatabaseHas('mass_intentions', [
            'schedule_id' => $schedule->id,
            'mass_date' => $schedule->mass_date->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_user_cannot_request_the_same_mass_schedule_twice(): void
    {
        $user = User::factory()->create();
        $schedule = $this->createOpenSchedule();
        $payload = [
            'intention_type' => 'Thanksgiving',
            'intention_for' => 'My family',
            'schedule_id' => $schedule->id,
            'mobile' => '9171234567',
            'offering_amount' => '100.00',
        ];

        $this->actingAs($user)
            ->post(route('mass-intentions.store'), $payload)
            ->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->post(route('mass-intentions.store'), $payload)
            ->assertSessionHasErrors('schedule_id');

        $this->assertSame(1, $user->massIntentions()->count());

        $this->actingAs($user)
            ->get(route('mass-intentions.index'))
            ->assertSee('Already requested');

        $user->massIntentions()->update(['status' => 'Rejected']);

        $this->actingAs($user)
            ->post(route('mass-intentions.store'), $payload)
            ->assertSessionHasNoErrors();
    }

    public function test_submitted_mass_intention_is_visible_to_admin_for_review(): void
    {
        $parishioner = User::factory()->create();
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);
        $schedule = $this->createOpenSchedule(now()->addDays(2));

        $this->actingAs($parishioner)
            ->post(route('mass-intentions.store'), [
                'intention_type' => 'Healing',
                'intention_for' => 'Maria Santos',
                'additional_message' => 'Please pray for a quick recovery.',
                'schedule_id' => $schedule->id,
                'mobile' => '9171234567',
                'offering_amount' => '150.00',
            ])
            ->assertRedirect(route('mass-intentions.confirmation', MassIntention::query()->sole()))
            ->assertSessionHas('status', 'Your Mass Intention request has been submitted.');

        $massIntention = MassIntention::query()->sole();

        $this->actingAs($admin)
            ->get(route('admin.mass-intentions', ['status' => 'Pending']))
            ->assertOk()
            ->assertSee('Pending review')
            ->assertSee('Maria Santos')
            ->assertSee('Please pray for a quick recovery.')
            ->assertSee('MI-'.$massIntention->created_at->format('Y').'-'.str_pad((string) $massIntention->id, 6, '0', STR_PAD_LEFT))
            ->assertSee(route('admin.mass-intentions.approve', $massIntention))
            ->assertSee(route('admin.mass-intentions.reject', $massIntention));
    }

    public function test_mass_intention_routes_connect_submission_admin_review_and_user_status(): void
    {
        $parishioner = User::factory()->create();
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);
        $schedule = $this->createOpenSchedule(now()->addDays(4));

        $this->actingAs($parishioner)
            ->post(route('mass-intentions.store'), [
                'intention_type' => 'Thanksgiving',
                'intention_for' => 'My family',
                'additional_message' => 'For health and peace.',
                'schedule_id' => $schedule->id,
                'mobile' => '9171234567',
                'offering_amount' => '100.00',
            ])
            ->assertRedirect(route('mass-intentions.confirmation', MassIntention::query()->sole()));

        $massIntention = MassIntention::query()->sole();

        $this->actingAs($admin)
            ->patch(route('admin.mass-intentions.approve', $massIntention))
            ->assertRedirect()
            ->assertSessionHas('mass_success');

        $this->patch(route('admin.mass-intentions.read-in-church', $massIntention))
            ->assertRedirect()
            ->assertSessionHas('mass_success');

        $this->actingAs($parishioner)
            ->get(route('mass-intentions.history'))
            ->assertOk()
            ->assertSee('MI-'.$massIntention->created_at->format('Y').'-'.str_pad((string) $massIntention->id, 6, '0', STR_PAD_LEFT))
            ->assertSee('Approved')
            ->assertSee('Read at the church')
            ->assertSee(route('users.my-request'));

        $this->assertDatabaseHas('mass_intentions', [
            'id' => $massIntention->id,
            'user_id' => $parishioner->id,
            'status' => 'Approved',
        ]);
    }

    public function test_the_form_and_the_list_are_on_separate_pages(): void
    {
        $user = User::factory()->create();
        MassIntention::factory()->create(['user_id' => $user->id, 'intention_for' => 'My family']);

        $this->actingAs($user)
            ->get(route('mass-intentions.index'))
            ->assertOk()
            ->assertSee('id="mass-intention-form"', false)
            ->assertSee('updateIntentionTypes', false)
            ->assertSee(route('mass-intentions.history'))
            ->assertDontSee('My family')
            ->assertDontSee('My Mass Intentions</h3>', false);

        $this->actingAs($user)
            ->get(route('mass-intentions.history'))
            ->assertOk()
            ->assertSee('My family')
            ->assertSee(route('mass-intentions.index'))
            ->assertDontSee('updateIntentionTypes', false)
            ->assertDontSee('id="mass-intention-form"', false);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee(route('mass-intentions.history'));

        auth()->logout();
        $this->get(route('mass-intentions.history'))->assertRedirect(route('login'));
    }

    public function test_mass_intention_page_lists_only_the_signed_in_users_requests(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownRequest = MassIntention::factory()->create([
            'user_id' => $user->id,
            'intention_for' => 'My family',
        ]);
        MassIntention::factory()->create([
            'user_id' => $otherUser->id,
            'intention_for' => 'Another family',
        ]);

        $this->actingAs($user)
            ->get(route('mass-intentions.history'))
            ->assertOk()
            ->assertSee(route('users.my-request'))
            ->assertSee('My family')
            ->assertDontSee('Another family')
            ->assertSee($ownRequest->intention_type)
            ->assertSee('MI-'.$ownRequest->created_at->format('Y').'-'.str_pad((string) $ownRequest->id, 6, '0', STR_PAD_LEFT));
    }

    public function test_dashboard_metrics_are_scoped_to_the_signed_in_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        MassIntention::factory()->create([
            'user_id' => $user->id,
            'status' => 'Pending',
        ]);
        MassIntention::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'Pending',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('activeMassIntentions', 1)
            ->assertViewHas('myRequests', 1)
            ->assertViewHas('massIntentions', fn ($intentions) => $intentions->count() === 1);
    }

    public function test_mass_intention_submission_validates_required_fields_and_values(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('mass-intentions.index'))
            ->post(route('mass-intentions.store'), [
                'intention_type' => 'Not a valid intention',
                'intention_for' => '',
                'schedule_id' => '',
                'mobile' => '123',
                'offering_amount' => '-1',
            ])
            ->assertRedirect(route('mass-intentions.index'))
            ->assertSessionHasErrors([
                'intention_type',
                'intention_for',
                'schedule_id',
                'mobile',
                'offering_amount',
            ]);

        $this->assertDatabaseCount('mass_intentions', 0);
    }

    public function test_guests_cannot_view_or_submit_mass_intentions(): void
    {
        $this->get(route('mass-intentions.index'))
            ->assertRedirect(route('login'));

        $this->post(route('mass-intentions.store'), [])
            ->assertRedirect(route('login'));
    }

    public function test_booking_confirmation_shows_reference_number_and_details(): void
    {
        $user = User::factory()->create();
        $schedule = $this->createOpenSchedule();

        $this->actingAs($user)->post(route('mass-intentions.store'), [
            'intention_type' => 'Thanksgiving',
            'intention_for' => 'The Santos family',
            'schedule_id' => $schedule->id,
            'mobile' => '9171234567',
            'offering_amount' => '100.00',
        ]);

        $massIntention = MassIntention::query()->sole();
        $reference = 'MI-'.$massIntention->created_at->format('Y').'-'.str_pad((string) $massIntention->id, 6, '0', STR_PAD_LEFT);

        $this->get(route('mass-intentions.confirmation', $massIntention))
            ->assertOk()
            ->assertSee('Booking Confirmation')
            ->assertSee('Booking received')
            ->assertSee($reference)
            ->assertSee('The Santos family')
            ->assertSee('Thanksgiving')
            ->assertSee($schedule->mass_date->format('l, F j, Y'))
            ->assertSee('100.00')
            ->assertSee('Pay at parish office');

        $this->get(route('mass-intentions.history'))
            ->assertSee(route('mass-intentions.confirmation', $massIntention))
            ->assertSee($reference);

        $massIntention->forceFill(['status' => 'Approved'])->save();

        $this->get(route('mass-intentions.confirmation', $massIntention))
            ->assertOk()
            ->assertSee('Booking confirmed');
    }

    public function test_booking_confirmation_is_private_to_its_owner(): void
    {
        $massIntention = MassIntention::factory()->create();

        $this->get(route('mass-intentions.confirmation', $massIntention))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('mass-intentions.confirmation', $massIntention))
            ->assertForbidden();
    }

    private function createOpenSchedule(?\Illuminate\Support\Carbon $massDate = null): MassSchedule
    {
        $massDate ??= now()->addDays(3);

        return MassSchedule::query()->create([
            'title' => 'Scheduled Mass',
            'mass_date' => $massDate,
            'closing_at' => $massDate->copy()->subHour(),
            'is_active' => true,
        ]);
    }
}
