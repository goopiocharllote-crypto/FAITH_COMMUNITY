<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\MassSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LivestreamTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_attach_a_livestream_link_to_a_schedule(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
        $massDate = now()->addDays(2)->setTime(8, 0);

        $this->actingAs($admin)->post(route('admin.schedules.store'), [
            'title' => 'Sunday Mass',
            'mass_date' => $massDate->format('Y-m-d\TH:i'),
            'closing_at' => $massDate->copy()->subDay()->format('Y-m-d\TH:i'),
            'livestream_url' => 'https://www.facebook.com/LobocParish/videos/123456789',
            'is_active' => '1',
        ])->assertRedirect(route('admin.schedules'));

        $schedule = MassSchedule::query()->sole();
        $this->assertSame('https://www.facebook.com/LobocParish/videos/123456789', $schedule->livestream_url);

        $this->actingAs($admin)->get(route('admin.schedules'))
            ->assertSee('Livestream link')
            ->assertSee('https://www.facebook.com/LobocParish/videos/123456789');

        $this->actingAs($admin)->patch(route('admin.schedules.update', $schedule), [
            'title' => 'Sunday Mass',
            'mass_date' => $massDate->format('Y-m-d\TH:i'),
            'closing_at' => $massDate->copy()->subDay()->format('Y-m-d\TH:i'),
            'livestream_url' => 'http://insecure.example.com/live',
            'is_active' => '1',
        ])->assertSessionHasErrors('livestream_url');
    }

    public function test_live_mass_page_embeds_the_stream_while_a_mass_is_live(): void
    {
        $user = User::factory()->create();
        MassSchedule::query()->create([
            'title' => 'Morning Mass',
            'mass_date' => now()->subMinutes(10),
            'closing_at' => now()->subDay(),
            'livestream_url' => 'https://www.youtube.com/watch?v=abcDEF12345',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('users.live-mass'))
            ->assertOk()
            ->assertSee('Live')
            ->assertSee('Morning Mass')
            ->assertSee('https://www.youtube.com/embed/abcDEF12345', false);
    }

    public function test_live_mass_page_lists_upcoming_livestream_links_when_nothing_is_live(): void
    {
        $user = User::factory()->create();
        MassSchedule::query()->create([
            'title' => 'Feast Day Mass',
            'mass_date' => now()->addDays(5),
            'closing_at' => now()->addDays(4),
            'livestream_url' => 'https://stream.example.org/feast',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('users.live-mass'))
            ->assertSee('No Live Mass Right Now')
            ->assertSee('https://stream.example.org/feast')
            ->assertSee('Livestream link');
    }

    public function test_announcement_pages_show_livestream_links(): void
    {
        Announcement::query()->create(['title' => 'Fiesta', 'content' => 'Join us.', 'is_active' => true]);
        MassSchedule::query()->create([
            'title' => 'Fiesta Mass',
            'mass_date' => now()->addDay(),
            'closing_at' => now()->addHours(12),
            'livestream_url' => 'https://www.facebook.com/LobocParish/videos/42',
            'is_active' => true,
        ]);
        MassSchedule::query()->create([
            'title' => 'Hidden Mass',
            'mass_date' => now()->addDay(),
            'closing_at' => now()->addHours(12),
            'livestream_url' => 'https://www.facebook.com/LobocParish/videos/99',
            'is_active' => false,
        ]);

        $this->get(route('landing'))
            ->assertSee('Watch Mass Online')
            ->assertSee('https://www.facebook.com/LobocParish/videos/42')
            ->assertDontSee('https://www.facebook.com/LobocParish/videos/99');

        $this->actingAs(User::factory()->create())
            ->get(route('users.announcements'))
            ->assertSee('Fiesta')
            ->assertSee('Watch livestream')
            ->assertSee('https://www.facebook.com/LobocParish/videos/42');
    }

    public function test_embed_urls_are_built_only_for_supported_video_links(): void
    {
        $cases = [
            'https://www.youtube.com/watch?v=abcDEF12345' => 'https://www.youtube.com/embed/abcDEF12345',
            'https://youtu.be/abcDEF12345' => 'https://www.youtube.com/embed/abcDEF12345',
            'https://www.youtube.com/live/abcDEF12345?si=x' => 'https://www.youtube.com/embed/abcDEF12345',
            'https://www.facebook.com/LobocParish/videos/123' => 'https://www.facebook.com/plugins/video.php?show_text=false&href='.rawurlencode('https://www.facebook.com/LobocParish/videos/123'),
            'https://www.facebook.com/LobocParish' => null,
            'https://zoom.us/j/123' => null,
        ];

        foreach ($cases as $url => $expected) {
            $schedule = new MassSchedule(['livestream_url' => $url]);
            $this->assertSame($expected, $schedule->livestreamEmbedUrl(), $url);
        }
    }
}
