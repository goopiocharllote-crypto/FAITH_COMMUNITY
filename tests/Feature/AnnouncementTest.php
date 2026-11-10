<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\MassSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_an_announcement(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.announcements.store'), [
                'title' => 'Parish Feast Day',
                'content' => 'Join us for the celebration.',
                'announcement_date' => '2026-11-15',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.announcements'))
            ->assertSessionHas('status', 'Announcement created successfully.');

        $this->assertDatabaseHas('announcements', [
            'title' => 'Parish Feast Day',
            'content' => 'Join us for the celebration.',
            'announcement_date' => '2026-11-15 00:00:00',
            'is_active' => 1,
        ]);
    }

    public function test_administrator_can_create_an_inactive_announcement(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.announcements.store'), [
                'title' => 'Internal notice',
                'content' => 'This should not be public.',
            ])
            ->assertRedirect(route('admin.announcements'));

        $this->assertDatabaseHas('announcements', [
            'title' => 'Internal notice',
            'is_active' => false,
        ]);
    }

    public function test_announcement_form_rejects_missing_and_invalid_fields(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.announcements'))
            ->post(route('admin.announcements.store'), [
                'title' => str_repeat('a', 256),
                'content' => '',
                'announcement_date' => 'not-a-date',
            ])
            ->assertRedirect(route('admin.announcements'))
            ->assertSessionHasErrors(['title', 'content', 'announcement_date']);

        $this->assertDatabaseCount('announcements', 0);
    }

    public function test_administrator_can_update_and_delete_an_announcement(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);
        $announcement = Announcement::create([
            'title' => 'Old title',
            'content' => 'Old content',
            'announcement_date' => '2026-11-01',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.announcements.update', $announcement), [
                'title' => 'Updated title',
                'content' => 'Updated content',
                'announcement_date' => '',
            ])
            ->assertRedirect(route('admin.announcements'))
            ->assertSessionHas('status', 'Announcement updated successfully.');

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Updated title',
            'content' => 'Updated content',
            'announcement_date' => null,
            'is_active' => false,
        ]);

        $this->delete(route('admin.announcements.destroy', $announcement))
            ->assertRedirect(route('admin.announcements'))
            ->assertSessionHas('status', 'Announcement deleted successfully.');

        $this->assertDatabaseMissing('announcements', [
            'id' => $announcement->id,
        ]);
    }

    public function test_admin_announcement_page_lists_active_and_inactive_records(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);
        Announcement::create([
            'title' => 'Visible announcement',
            'content' => 'Visible content',
            'is_active' => true,
        ]);
        Announcement::create([
            'title' => 'Hidden announcement',
            'content' => 'Hidden content',
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.announcements'))
            ->assertOk()
            ->assertSee('Visible announcement')
            ->assertSee('Hidden announcement');
    }

    public function test_public_announcement_pages_only_show_active_records(): void
    {
        Announcement::create([
            'title' => 'Public announcement',
            'content' => 'Public content',
            'is_active' => true,
        ]);
        Announcement::create([
            'title' => 'Private announcement',
            'content' => 'Private content',
            'is_active' => false,
        ]);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('Public announcement')
            ->assertDontSee('Private announcement');

        $this->actingAs(User::factory()->create())
            ->get(route('users.announcements'))
            ->assertOk()
            ->assertSee('Public announcement')
            ->assertDontSee('Private announcement');
    }

    public function test_parishioner_announcements_show_only_active_upcoming_mass_schedules(): void
    {
        $user = User::factory()->create();
        $massDate = now()->addDays(2);
        MassSchedule::query()->create([
            'title' => 'Sunday Morning Mass',
            'mass_date' => $massDate,
            'closing_at' => $massDate->copy()->subHour(),
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
            ->get(route('users.announcements'))
            ->assertOk()
            ->assertSee('Upcoming Mass Schedules')
            ->assertSee('Sunday Morning Mass')
            ->assertSee('Intention requests close:')
            ->assertSee($massDate->copy()->subHour()->format('l, F j, Y · g:i A'))
            ->assertSee(route('mass-intentions.index', [
                'schedule_id' => MassSchedule::query()->where('title', 'Sunday Morning Mass')->value('id'),
            ]))
            ->assertDontSee('Hidden Mass')
            ->assertDontSee('Past Mass');

        $this->get(route('mass-intentions.index', [
            'schedule_id' => MassSchedule::query()->where('title', 'Sunday Morning Mass')->value('id'),
        ]))
            ->assertOk()
            ->assertSee('data-schedule-id="'.MassSchedule::query()->where('title', 'Sunday Morning Mass')->value('id').'"', false);
    }

    public function test_parishioner_dashboard_shows_only_active_announcements(): void
    {
        Announcement::create([
            'title' => 'Dashboard announcement',
            'content' => 'Visible on the dashboard.',
            'is_active' => true,
        ]);
        Announcement::create([
            'title' => 'Hidden dashboard announcement',
            'content' => 'Not visible on the dashboard.',
            'is_active' => false,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard announcement')
            ->assertDontSee('Hidden dashboard announcement');
    }

    public function test_non_administrators_cannot_manage_announcements(): void
    {
        $announcement = Announcement::create([
            'title' => 'Protected announcement',
            'content' => 'Protected content',
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.announcements.store'), [
                'title' => 'Unauthorized',
                'content' => 'Unauthorized content',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('announcements', [
            'title' => 'Unauthorized',
        ]);

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Protected announcement',
        ]);
    }
}
