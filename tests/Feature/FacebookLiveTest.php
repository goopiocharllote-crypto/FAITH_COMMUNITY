<?php

namespace Tests\Feature;

use App\Models\FacebookLive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FacebookLiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Never reach the real Facebook from tests.
        Http::preventStrayRequests();
    }

    /** Short links that don't redirect anywhere (the link is kept as typed). */
    private function fakeFacebookWithoutRedirects(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
    }

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
            ->assertSessionHas('status', 'Facebook Live link saved. Parishioners can now watch the video on their Live Mass page.');

        $live = FacebookLive::current();
        $this->assertNotNull($live);
        $this->assertSame('Sunday 9 AM Mass', $live->title);
        $this->assertSame('https://www.facebook.com/lobocparish/videos/1234567890', $live->url);
        $this->assertTrue($live->is_live);
        $this->assertSame($admin->id, $live->posted_by);
    }

    public function test_posting_a_new_link_replaces_the_old_one(): void
    {
        $this->fakeFacebookWithoutRedirects();
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
        $this->fakeFacebookWithoutRedirects();

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
            'url' => 'https://www.facebook.com/lobocparish/live',
            'is_live' => true,
        ]);

        $this->actingAs($this->parishioner())
            ->get(route('users.live-mass'))
            ->assertOk()
            ->assertSee('Live now on Facebook')
            ->assertSee('Sunday 9 AM Mass')
            ->assertSee('Watch on Facebook')
            ->assertSee('href="https://www.facebook.com/lobocparish/live"', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertSee("We're live on Facebook", false)
            ->assertSee('data-live-indicator', false);
    }

    public function test_link_marked_not_live_shows_as_replay_without_live_badges(): void
    {
        FacebookLive::query()->create([
            'url' => 'https://www.facebook.com/lobocparish/live',
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

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function embeddableLinks(): array
    {
        return [
            'page video' => ['https://www.facebook.com/lobocparish/videos/1234567890', true],
            'page video with slug' => ['https://www.facebook.com/lobocparish/videos/sunday-mass/1234567890/', true],
            'top-level video' => ['https://www.facebook.com/videos/1234567890', true],
            'watch link' => ['https://www.facebook.com/watch/?v=1234567890', true],
            'watch live link' => ['https://www.facebook.com/watch/live/?v=1234567890', true],
            'mobile video' => ['https://m.facebook.com/lobocparish/videos/1234567890', true],
            'reel' => ['https://www.facebook.com/reel/1234567890', true],
            'page live link' => ['https://www.facebook.com/lobocparish/live', false],
            'page home' => ['https://www.facebook.com/lobocparish', false],
            'watch without id' => ['https://www.facebook.com/watch/', false],
            'short link' => ['https://fb.watch/abcDEF123/', false],
            'share link' => ['https://www.facebook.com/share/v/1AbCdEf/', false],
        ];
    }

    /**
     * @dataProvider embeddableLinks
     */
    public function test_only_specific_video_links_are_embeddable(string $url, bool $embeddable): void
    {
        $this->assertSame($embeddable, FacebookLive::isEmbeddableVideoUrl($url));
    }

    public function test_parishioner_sees_the_live_video_player_instead_of_the_placeholder(): void
    {
        $url = 'https://www.facebook.com/lobocparish/videos/1234567890';
        FacebookLive::query()->create(['title' => 'Sunday 9 AM Mass', 'url' => $url, 'is_live' => true]);

        $this->actingAs($this->parishioner())
            ->get(route('users.live-mass'))
            ->assertOk()
            ->assertSee('data-facebook-player', false)
            ->assertSee('https://www.facebook.com/plugins/video.php?href='.rawurlencode($url), false)
            ->assertSee('allowfullscreen', false)
            ->assertSee('Open in Facebook')
            ->assertDontSee("We're live on Facebook", false)
            ->assertDontSee('No Live Mass Right Now');
    }

    public function test_replay_video_is_still_embedded_with_a_replay_label(): void
    {
        FacebookLive::query()->create(['url' => 'https://www.facebook.com/watch/?v=1234567890', 'is_live' => false]);

        $this->actingAs($this->parishioner())
            ->get(route('users.live-mass'))
            ->assertOk()
            ->assertSee('data-facebook-player', false)
            ->assertSee('Replay');
    }

    public function test_page_links_fall_back_to_the_watch_on_facebook_button(): void
    {
        FacebookLive::query()->create(['url' => 'https://www.facebook.com/lobocparish/live', 'is_live' => true]);

        $this->actingAs($this->parishioner())
            ->get(route('users.live-mass'))
            ->assertOk()
            ->assertDontSee('data-facebook-player', false)
            ->assertSee('Watch on Facebook')
            ->assertSee("We're live on Facebook", false);
    }

    public function test_admin_is_told_whether_the_link_plays_on_the_page(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.live-mass.update'), ['url' => 'https://www.facebook.com/lobocparish/live', 'is_live' => '1'])
            ->assertSessionHas('status', fn (string $status) => str_contains($status, 'Watch on Facebook'));

        $this->actingAs($admin)
            ->get(route('admin.live-mass'))
            ->assertSee('This is a page link');

        $this->actingAs($admin)
            ->put(route('admin.live-mass.update'), ['url' => 'https://www.facebook.com/lobocparish/videos/1234567890', 'is_live' => '1']);

        $this->actingAs($admin)
            ->get(route('admin.live-mass'))
            ->assertSee('The video plays right on the parishioners', false)
            ->assertSee('plugins/video.php', false);
    }

    public function test_share_links_are_resolved_to_the_real_video_link(): void
    {
        Http::fake([
            'www.facebook.com/share/v/*' => Http::response('', 302, ['Location' => 'https://www.facebook.com/lobocparish/videos/987654321/?mibextid=abc&rdid=xyz']),
            '*' => Http::response('', 200),
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.live-mass.update'), ['url' => 'https://www.facebook.com/share/v/1AbCdEf/', 'is_live' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame('https://www.facebook.com/lobocparish/videos/987654321/', FacebookLive::current()->url);
        $this->assertNotNull(FacebookLive::current()->embedUrl());
    }

    public function test_fb_watch_links_follow_multiple_redirects(): void
    {
        Http::fake([
            'fb.watch/*' => Http::response('', 301, ['Location' => 'https://m.facebook.com/watch/?v=5555&ref=sharing']),
            'm.facebook.com/*' => Http::response('', 302, ['Location' => '/watch/?v=5555']),
            '*' => Http::response('', 200),
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.live-mass.update'), ['url' => 'https://fb.watch/abcDEF123/', 'is_live' => '1']);

        $this->assertSame('https://m.facebook.com/watch/?v=5555', FacebookLive::current()->url);
    }

    public function test_share_links_never_resolve_to_other_sites(): void
    {
        Http::fake([
            'www.facebook.com/share/*' => Http::response('', 302, ['Location' => 'https://evil.example/videos/123']),
            '*' => Http::response('', 200),
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.live-mass.update'), ['url' => 'https://www.facebook.com/share/v/1AbCdEf/', 'is_live' => '1']);

        $this->assertSame('https://www.facebook.com/share/v/1AbCdEf/', FacebookLive::current()->url);
    }

    public function test_link_is_still_saved_when_facebook_cannot_be_reached(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'));

        $this->actingAs($this->admin())
            ->put(route('admin.live-mass.update'), ['url' => 'https://fb.watch/abcDEF123/', 'is_live' => '1'])
            ->assertRedirect(route('admin.live-mass'));

        $this->assertSame('https://fb.watch/abcDEF123/', FacebookLive::current()->url);
    }
}
