<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\MassIntention;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_pages_render_with_laravel_vite_assets(): void
    {
        $loginResponse = $this->get(route('login'));
        $cacheControl = $loginResponse->headers->get('Cache-Control');

        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('private', $cacheControl);

        $loginResponse
            ->assertOk()
            ->assertHeader('Pragma', 'no-cache')
            ->assertSee('Sign in to your account')
            ->assertSee('Sign in as')
            ->assertSee('value="parishioner"', false)
            ->assertSee('value="administrator"', false)
            ->assertSee(route('login.store'))
            ->assertSee('<svg', false)
            ->assertSee('data-password-icon-show', false)
            ->assertSee('data-password-icon-hide', false)
            ->assertSee('name="_token"', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create your account')
            ->assertSee(route('register.store'))
            ->assertSee('<svg', false)
            ->assertSee('Mobile number')
            ->assertSee('name="_token"', false);
    }

    public function test_a_user_can_register_and_is_logged_in(): void
    {
        $response = $this->followingRedirects()->post(route('register.store'), [
            'name' => 'Parish Member',
            'email' => 'member@example.com',
            'mobile' => '9171234567',
            'password' => 'ParishPassword123!',
            'password_confirmation' => 'ParishPassword123!',
        ]);

        $user = User::where('email', 'member@example.com')->firstOrFail();

        $response->assertOk()
            ->assertSee('Your account has been created successfully. You are now signed in.')
            ->assertSee('Welcome, Parish Member!')
            ->assertSee('Log out');
        $this->assertAuthenticatedAs($user);
        $this->assertSame('9171234567', $user->mobile);
        $this->assertSame(User::ROLE_PARISHIONER, $user->role);
        $this->assertTrue(Hash::check('ParishPassword123!', $user->password));
    }

    public function test_registration_rejects_invalid_or_duplicate_member_details(): void
    {
        User::factory()->create([
            'email' => 'member@example.com',
            'mobile' => '9171234567',
        ]);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Another Member',
                'email' => 'member@example.com',
                'mobile' => '8123456789',
                'password' => 'ParishPassword123!',
                'password_confirmation' => 'ParishPassword123!',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email', 'mobile']);
    }

    public function test_a_member_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'password' => Hash::make('ParishPassword123!'),
        ]);

        $this->post(route('login.store'), [
            'role' => User::ROLE_PARISHIONER,
            'email' => 'member@example.com',
            'password' => 'ParishPassword123!',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))
            ->assertRedirect(route('landing'));

        $this->assertGuest();
    }

    public function test_login_redirects_to_dashboard_even_if_another_intended_url_is_stored(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('ParishPassword123!'),
        ]);

        $this->withSession(['url.intended' => route('landing')])
            ->post(route('login.store'), [
                'role' => User::ROLE_PARISHIONER,
                'email' => $user->email,
                'password' => 'ParishPassword123!',
            ])
            ->assertRedirect(route('dashboard'));
    }

    public function test_authenticated_users_can_open_their_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Welcome back, '.$user->name.'!')
            ->assertSee('Upcoming Masses')
            ->assertSee('Upcoming Mass Schedule');
    }

    public function test_guests_are_redirected_to_login_when_opening_the_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_an_administrator_can_log_in_to_the_administrator_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
            'password' => Hash::make('ParishPassword123!'),
        ]);

        $this->post(route('login.store'), [
            'role' => User::ROLE_ADMINISTRATOR,
            'email' => $user->email,
            'password' => 'ParishPassword123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin Dashboard | Faith Community Platform')
            ->assertSee('Welcome back, '.$user->name)
            ->assertSee('Total Mass Intention')
            ->assertSee('No upcoming Mass Schedule');

        $this->post(route('logout'))
            ->assertRedirect(route('landing'));

        $this->assertGuest();
    }

    public function test_administrator_sidebar_connects_and_renders_every_admin_page(): void
    {
        $routes = [
            'admin.dashboard',
            'admin.announcements',
            'admin.mass-intentions',
            'admin.schedules',
            'admin.prayer-requests',
            'admin.donations',
            'admin.reports',
            'admin.printlist',
            'admin.user-activity',
            'admin.settings',
        ];

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);
        $parishioner = User::factory()->create();
        MassIntention::factory()->create([
            'user_id' => $parishioner->id,
            'status' => 'Pending',
        ]);
        MassIntention::factory()->create([
            'user_id' => $parishioner->id,
            'status' => 'Approved',
        ]);
        $this->actingAs($admin);

        foreach ($routes as $route) {
            $response = $this->get(route($route));

            $response
                ->assertOk()
                ->assertSee('aria-label="Administrator navigation"', false)
                ->assertSee('aria-current="page"', false);

            if ($route === 'admin.announcements') {
                $response->assertSee('Create, edit, and delete parish announcements');
            }
        }
    }

    public function test_administrator_can_approve_a_mass_intention_from_the_admin_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);
        $massIntention = MassIntention::factory()->create([
            'status' => 'Pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.mass-intentions.approve', $massIntention))
            ->assertRedirect()
            ->assertSessionHas('mass_success', 'Mass Intention approved successfully.');

        $this->assertDatabaseHas('mass_intentions', [
            'id' => $massIntention->id,
            'status' => 'Approved',
        ]);
    }

    public function test_login_rejects_a_selected_role_that_does_not_match_the_account(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('ParishPassword123!'),
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'role' => User::ROLE_ADMINISTRATOR,
                'email' => $user->email,
                'password' => 'ParishPassword123!',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_parishioners_cannot_open_the_administrator_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_administrators_cannot_open_the_parishioner_dashboard(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]))
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_displays_database_metrics_and_recent_requests(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMINISTRATOR,
        ]);
        $parishioner = User::factory()->create();

        MassIntention::factory()->create([
            'user_id' => $parishioner->id,
            'status' => 'Pending',
            'intention_for' => 'The Santos family',
            'created_at' => now(),
        ]);
        MassIntention::factory()->create([
            'user_id' => $parishioner->id,
            'status' => 'Approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('totalMassIntentions', 2)
            ->assertViewHas('pendingRequests', 1)
            ->assertViewHas('approvedRequests', 1)
            ->assertViewHas('recentActivities', fn ($activities) => $activities->count() === 2)
            ->assertSee('The Santos family');
    }

    public function test_authenticated_users_can_open_their_account_pages(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['users.announcements', 'users.live-mass', 'users.my-request', 'users.profile', 'virtual-candle', 'donations.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_user_navigation_renders_responsive_links(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.profile'))
            ->assertOk()
            ->assertSee('aria-controls="user-navigation"', false)
            ->assertSee('data-mobile-menu-flex', false)
            ->assertSee(route('dashboard'))
            ->assertSee(route('mass-intentions.index'))
            ->assertDontSee('href="'.route('donations.index').'"', false)
            ->assertSee(route('virtual-candle'))
            ->assertSee(route('users.announcements'))
            ->assertSee(route('users.live-mass'))
            ->assertSee(route('users.my-request'))
            ->assertSee(route('users.profile'));
    }

    public function test_donation_routes_open_the_record_form(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('donations.index'))
            ->assertOk()
            ->assertSee('Make a Donation')
            ->assertSee('Donate online · GCash')
            ->assertSee('Donate at the parish')
            ->assertSee(route('donations.store'));

        $this->get(route('donation'))
            ->assertRedirect(route('donations.index'));

        $this->from(route('donations.index'))
            ->post(route('donations.store'), [
                'amount' => 100,
                'payment_method' => 'Cash',
            ])
            ->assertRedirect(route('donations.index'))
            ->assertSessionHas('status');
    }

    public function test_virtual_candle_uses_the_responsive_user_navigation(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('virtual-candle'))
            ->assertOk()
            ->assertSee('aria-controls="user-navigation"', false)
            ->assertSee('data-mobile-menu-flex', false)
            ->assertSee('id="candle"', false)
            ->assertSee(route('virtual-candle'));
    }

    public function test_invalid_credentials_do_not_authenticate(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), [
                'role' => User::ROLE_PARISHIONER,
                'email' => 'missing@example.com',
                'password' => 'incorrect-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
