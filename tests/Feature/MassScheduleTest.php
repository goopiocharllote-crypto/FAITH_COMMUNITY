<?php

namespace Tests\Feature;

use App\Models\MassSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MassScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_update_and_delete_mass_schedules(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);
        $massDate = now()->addDays(3)->setTime(23, 30);
        $closingAt = now()->addDays(2)->setTime(12, 45);
        $this->actingAs($admin);

        $this->get(route('admin.schedules'))
            ->assertOk()
            ->assertSee('Add Mass Schedule')
            ->assertSee('type="date"', false)
            ->assertSee('12-hour clock')
            ->assertSee('Closing AM or PM')
            ->assertSee('aria-label="Mass hour"', false)
            ->assertSee('aria-label="Mass minute"', false)
            ->assertSee('aria-label="Closing hour"', false)
            ->assertSee('aria-label="Closing minute"', false)
            ->assertSee('value="PM"', false)
            ->assertSee('No Mass schedules yet.');

        $this->post(route('admin.schedules.store'), [
            'title' => 'Sunday Morning Mass',
            'mass_date' => $massDate->format('Y-m-d\TH:i'),
            'closing_at' => $closingAt->format('Y-m-d\TH:i'),
            'is_active' => '1',
        ])
            ->assertRedirect(route('admin.schedules'))
            ->assertSessionHas('status', 'Mass schedule created successfully.');

        $schedule = MassSchedule::query()->sole();
        $this->assertTrue($schedule->is_active);
        $this->assertSame('23:30', $schedule->mass_date->format('H:i'));
        $this->assertSame('12:45', $schedule->closing_at->format('H:i'));

        $this->get(route('admin.schedules'))
            ->assertOk()
            ->assertSee('Sunday Morning Mass')
            ->assertSee($massDate->format('Y-m-d\TH:i'))
            ->assertDontSee('Maximum Intentions')
            ->assertSee(route('admin.schedules.update', $schedule))
            ->assertSee(route('admin.schedules.destroy', $schedule));

        $this->patch(route('admin.schedules.update', $schedule), [
            'title' => 'Sunday Family Mass',
            'mass_date' => now()->addDays(5)->setTime(13, 15)->format('Y-m-d\TH:i'),
            'closing_at' => now()->addDays(4)->setTime(11, 30)->format('Y-m-d\TH:i'),
        ])
            ->assertRedirect(route('admin.schedules'))
            ->assertSessionHas('status', 'Mass schedule updated successfully.');

        $this->assertDatabaseHas('mass_schedules', [
            'id' => $schedule->id,
            'title' => 'Sunday Family Mass',
            'closing_at' => now()->addDays(4)->setTime(11, 30)->format('Y-m-d H:i:00'),
            'is_active' => false,
        ]);

        $this->delete(route('admin.schedules.destroy', $schedule))
            ->assertRedirect(route('admin.schedules'))
            ->assertSessionHas('status', 'Mass schedule deleted successfully.');

        $this->assertDatabaseMissing('mass_schedules', ['id' => $schedule->id]);
    }

    public function test_mass_schedule_validation_rejects_invalid_values(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.schedules'))
            ->post(route('admin.schedules.store'), [
                'title' => '',
                'mass_date' => now()->subDay()->format('Y-m-d\TH:i'),
                'closing_at' => now()->subDays(2)->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('admin.schedules'))
            ->assertSessionHasErrors(['title', 'mass_date']);

        $this->assertDatabaseCount('mass_schedules', 0);
    }

    public function test_schedule_closing_time_must_precede_mass_start(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);
        $massDate = now()->addDays(2);

        $this->actingAs($admin)
            ->from(route('admin.schedules'))
            ->post(route('admin.schedules.store'), [
                'title' => 'Sunday Mass',
                'mass_date' => $massDate->format('Y-m-d\TH:i'),
                'closing_at' => $massDate->copy()->addHour()->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('admin.schedules'))
            ->assertSessionHasErrors('closing_at');

        $this->assertDatabaseCount('mass_schedules', 0);
    }

    public function test_schedule_time_requires_the_native_datetime_picker_format(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.schedules'))
            ->post(route('admin.schedules.store'), [
                'title' => 'Evening Mass',
                'mass_date' => now()->addDays(2)->format('Y-m-d H:i'),
                'closing_at' => now()->addDay()->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('admin.schedules'))
            ->assertSessionHasErrors('mass_date');

        $this->assertDatabaseCount('mass_schedules', 0);
    }

    public function test_active_upcoming_schedules_are_shown_to_parishioners_and_dashboards(): void
    {
        $user = User::factory()->create();
        $schedule = MassSchedule::query()->create([
            'title' => 'Saturday Vigil',
            'mass_date' => now()->addDays(2),
            'closing_at' => now()->addDay(),
            'is_active' => true,
        ]);
        MassSchedule::query()->create([
            'title' => 'Hidden Mass',
            'mass_date' => now()->addDays(3),
            'is_active' => false,
        ]);
        MassSchedule::query()->create([
            'title' => 'Past Mass',
            'mass_date' => now()->subDay(),
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Saturday Vigil')
            ->assertDontSee('Hidden Mass')
            ->assertDontSee('Past Mass');

        $this        ->get(route('users.live-mass'))
        ->assertOk()
        ->assertSee('Saturday Vigil')
        ->assertDontSee('30 intentions')
            ->assertDontSee('Hidden Mass')
            ->assertDontSee('Past Mass');

        $this->actingAs($user)
            ->get(route('mass-intentions.index'))
            ->assertOk()
            ->assertSee($schedule->title)
            ->assertSee($schedule->closing_at->format('M j, Y · g:i A'));

    }

    public function test_admin_dashboard_shows_upcoming_schedules_and_parishioners_cannot_manage_them(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);
        $parishioner = User::factory()->create();
        $schedule = MassSchedule::query()->create([
            'title' => 'Weekday Mass',
            'mass_date' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Weekday Mass');

        $this->actingAs($parishioner)
            ->get(route('admin.schedules'))
            ->assertForbidden();

        $this->post(route('admin.schedules.store'), [])
            ->assertForbidden();

        $this->assertDatabaseHas('mass_schedules', ['id' => $schedule->id]);
    }
}
