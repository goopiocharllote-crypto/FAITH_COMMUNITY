<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('FAITH COMMUNITY')
            ->assertSee('id="home"', false)
            ->assertSee('id="services"', false)
            ->assertSee('id="about"', false)
            ->assertSee('id="announcements"', false)
            ->assertSee('id="contact"', false)
            ->assertSee('data-mobile-menu-toggle', false)
            ->assertSee('id="mobile-navigation"', false)
            ->assertSee('aria-label="Footer navigation"', false)
            ->assertSee('All rights reserved.')
            ->assertSee(route('landing') . '#home')
            ->assertSee('Our Parish Services')
            ->assertSee('About the Parish')
            ->assertSee('Parish Announcements')
            ->assertSee('Contact Information')
            ->assertSee(route('landing') . '#about')
            ->assertSee(route('landing') . '#services')
            ->assertSee(route('landing') . '#announcements')
            ->assertSee(route('landing') . '#contact')
            ->assertSee(route('login'))
            ->assertSee(route('register'))
            ->assertSee(route('mass-intentions.index'))
            ->assertSee(route('prayer-requests.index'))
            ->assertSee(route('virtual-candle'))
            ->assertSee(route('donations.index'));
    }

    public function test_section_routes_redirect_to_the_matching_home_section(): void
    {
        $sectionRoutes = [
            '/about' => '/#about',
            '/services' => '/#services',
            '/announcements' => '/#announcements',
            '/contact' => '/#contact',
        ];

        foreach ($sectionRoutes as $url => $destination) {
            $this->get($url)->assertRedirect($destination);
        }
    }

    public function test_legacy_navigation_urls_redirect_to_the_named_pages(): void
    {
        $legacyUrls = [
            '/about-parish' => '/#about',
            '/parish-services' => '/#services',
            '/parish-announcements' => '/#announcements',
            '/parish-contact' => '/#contact',
        ];

        foreach ($legacyUrls as $url => $destination) {
            $this->get($url)->assertRedirect($destination);
        }
    }
}
