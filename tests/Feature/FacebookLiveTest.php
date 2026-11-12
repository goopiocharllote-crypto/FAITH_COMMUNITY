<?php

namespace Tests\Feature;

use App\Models\FacebookLive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacebookLiveTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
    }

    private function parishioner(): User
    {
        return User::factory()->create(['role' => User::ROLE_PARISHIONER]);
    }

    public function test_admin_sees_live_mass_page_in_the_portal(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.live-mass'))
            ->assertOk()
            ->assertSee('Post a Facebook Live link')
            ->assertSee('No Facebook Live link is posted.')
            ->assertSee(route('admin.live-mass'));
    }

    public function test_admin_can_post_a_facebook_live_link(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.live-mass.update'), [
                'title' => 'Sunday 9 AM Mass',
                'url' => '  https://www.facebook.com/lobocparish/videos/1234567890  ',
                'is_live' => '1',
            ])
            ->assertRedirect(route('admin.live-mass'))
            ->assertSessionHas('status', 'Facebook Live link saved. Parishioners can now see it on their Live Mass page.');

        $live = FacebookLive::current();
        $this->assertNotNull($live);
        $this->assertSame('Sunday 9 AM Mass', $live->title);
        $this->assertSame('https://www.facebook.com/lobocparish/videos/1234567890', $live->url);
        $this->assertTrue($live->is_live);
        $this->assertSame($admin->id, $live->posted_by);
    }

    public function test_posting_a_new_link_replaces_the_old_one(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.live-mass.update'), [
            'url' => 'https://www.facebook.com/lobocparish/videos/111',
            'is_live' => '1',
        ]);
        $this->actingAs($admin)->put(route('admin.live-mass.update'), [
            'url' => 'https://fb.watch/abcDEF123/',
            'is_live' => '0',
        ]);

        $this->assertDatabaseCount('facebook_lives', 1);
        $this->assertSame('https://fb.watch/abcDEF123/', FacebookLive::current()->url);
        $this->assertFalse(FacebookLive::current()->is_live);
        $this->assertNull(FacebookLive::current()->title);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function acceptedUrls(): array
    {
        return [
            'www page video' => ['https://www.facebook.com/lobocparish/videos/1234567890'],
            'mobile site' => ['https://m.facebook.com/lobocparish/live'],
            'watch link' => ['https://www.facebook.com/watch/live/?v=1234567890'],
            'fb.watch short link' => ['https://fb.watch/abcDEF123/'],
            'fb.com' => ['https://fb.com/lobocparish/videos/123'],
            'share link' => ['https://www.facebook.com/share/v/1AbCdEf/'],
        ];
    }

    /**
     * @dataProvider acceptedUrls
     */
    public function test_facebook_links_are_accepted(string $url): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.live-mass.update'), ['url' => $url, 'is_live' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame($url, FacebookLive::current()->url);
    }

    /**
     * @return array<string, array{0: array<string, string>, 1: string}>
     */
    public static function invalidSubmissions(): array
    {
        return [
            'missing link' => [['url' => ''], 'url'],
            'not a url' => [['url' => 'facebook live tonight'], 'url'],
            'http instead of https' => [['url' => 'http://www.facebook.com/lobocparish/live'], 'url'],
            'youtube link' => [['url' => 'https://www.youtube.com/watch?v=abc123'], 'url'],
            'look-alike domain' => [['url' => 'https://facebook.com.evil.example/live'], 'url'],
            'domain suffix trick' => [['url' => 'https://notfacebook.com/live'], 'url'],
            'javascript scheme' => [['url' => 'javascript:alert(1)'], 'url'],
            'link too long' => [['url' => 'https://www.facebook.com/'.str_repeat('a', 500)], 'url'],
            'title too long' => [['url' => 'https://www.facebook.com/lobocparish/live', 'title' => str_repeat('a', 121)], 'title'],
        ];
    }

    /**
     * @dataProvider invalidSubmissions
     *
     * @param  array<string, string>  $payload
     */
    public function test_invalid_links_are_rejected(array $payload, string $field): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.live-mass'))
            ->put(route('admin.live-mass.update'), $payload)
            ->assertRedirect(route('admin.live-mass'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('facebook_lives', 0);
    }

    public function test_admin_can_remove_the_link(): void
    {
        FacebookLive::query()->create(['url' => 'https://www.facebook.com/lobocparish/live', 'is_live' => true]);

        $this->actingAs($this->admin())
            ->delete(route('admin.live-mass.destroy'))
            ->assertRedirect(route('admin.live-mass'))
            ->assertSessionHas('status', 'Facebook Live link removed. Parishioners will no longer see it.');

        $this->assertDatabaseCount('facebook_lives', 0);
    }

    public function test_parishioners_and_guests_cannot_manage_the_link(): void
    {
        $this->actingAs($this->parishioner())
            ->get(route('admin.live-mass'))
            ->assertForbidden();
        $this->actingAs($this->parishioner())
            ->put(route('admin.live-mass.update'), ['url' => 'https://www.facebook.com/x/live', 'is_live' => '1'])
            ->assertForbidden();
        $this->actingAs($this->parishioner())
            ->delete(route('admin.live-mass.destroy'))
            ->assertForbidden();

        auth()->logout();

        $this->put(route('admin.live-mass.update'), ['url' => 'https://www.facebook.com/x/live'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('facebook_lives', 0);
    }

    public function test_parishioner_sees_live_link_that_opens_facebook(): void
    {
        FacebookLive::query()->create([
            'title' => 'Sunday 9 AM Mass',
            'url' => 'https://www.facebook.com/lobocparish/videos/1234567890',
            'is_live' => true,
        ]);

        $this->actingAs($this->parishioner())
            ->get(route('users.live-mass'))
            ->assertOk()
            ->assertSee('Live now on Facebook')
            ->assertSee('Sunday 9 AM Mass')
            ->assertSee('Watch on Facebook')
            ->assertSee('href="https://www.facebook.com/lobocparish/videos/1234567890"', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertSee("We're live on Facebook", false)
            ->assertSee('data-live-indicator', false);
    }

    public function test_link_marked_not_live_shows_as_replay_without_live_badges(): void
    {
        FacebookLive::query()->create([
            'url' => 'https://www.facebook.com/lobocparish/videos/1234567890',
            'is_live' => false,
        ]);

        $this->actingAs($this->parishioner())
            ->get(route('users.live-mass'))
            ->assertOk()
            ->assertSee(FacebookLive::DEFAULT_TITLE)
            ->assertSee('Watch on Facebook')
            ->assertDontSee('Live now on Facebook')
            ->assertDontSee('data-live-indicator', false)
            ->assertSee('No Live Mass Right Now');
    }

    public function test_no_facebook_card_when_no_link_is_posted(): void
    {
        $this->actingAs($this->parishioner())
            ->get(route('users.live-mass'))
            ->assertOk()
            ->assertDontSee('data-facebook-live', false)
            ->assertDontSee('Watch on Facebook')
            ->assertSee('No Live Mass Right Now');
    }

    public function test_live_badge_appears_in_navbar_on_other_parishioner_pages(): void
    {
        FacebookLive::query()->create(['url' => 'https://www.facebook.com/lobocparish/live', 'is_live' => true]);

        $this->actingAs($this->parishioner())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-live-indicator', false);
    }
}
