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

    public function test_registration_normalizes_name_email_and_mobile(): void
    {
        $this->post(route('register.store'), [
            'name' => '  María   Dela Cruz-Santos ',
            'email' => '  Maria.Santos@Example.COM ',
            'mobile' => '+63 917-123-4567',
            'password' => 'ParishPassword123',
            'password_confirmation' => 'ParishPassword123',
        ])->assertRedirect(route('dashboard'));

        $user = User::firstOrFail();

        $this->assertSame('María Dela Cruz-Santos', $user->name);
        $this->assertSame('maria.santos@example.com', $user->email);
        $this->assertSame('9171234567', $user->mobile);
        $this->assertAuthenticatedAs($user);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function acceptedMobileFormats(): array
    {
        return [
            'local 10 digits' => ['9171234567', '9171234567'],
            'leading zero' => ['09171234567', '9171234567'],
            'country code' => ['639171234567', '9171234567'],
            'plus country code with spaces' => ['+63 917 123 4567', '9171234567'],
            'dashes and parentheses' => ['(0917) 123-4567', '9171234567'],
        ];
    }

    /**
     * @dataProvider acceptedMobileFormats
     */
    public function test_registration_accepts_common_mobile_formats(string $input, string $stored): void
    {
        $this->post(route('register.store'), [
            'name' => 'Parish Member',
            'email' => 'member@example.com',
            'mobile' => $input,
            'password' => 'ParishPassword123',
            'password_confirmation' => 'ParishPassword123',
        ])->assertSessionHasNoErrors();

        $this->assertSame($stored, User::firstOrFail()->mobile);
    }

    public function test_registration_treats_email_case_insensitively_for_duplicates_and_login(): void
    {
        User::factory()->create([
            'email' => 'member@example.com',
            'password' => Hash::make('ParishPassword123'),
        ]);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Parish Member',
                'email' => 'Member@Example.com',
                'mobile' => '9181234567',
                'password' => 'ParishPassword123',
                'password_confirmation' => 'ParishPassword123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email' => 'An account with this email address already exists. Try signing in instead.']);

        $this->post(route('login.store'), [
            'role' => User::ROLE_PARISHIONER,
            'email' => ' MEMBER@example.com',
            'password' => 'ParishPassword123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    /**
     * @return array<string, array{0: array<string, string>, 1: string}>
     */
    public static function invalidRegistrations(): array
    {
        $valid = [
            'name' => 'Parish Member',
            'email' => 'member@example.com',
            'mobile' => '9171234567',
            'password' => 'ParishPassword123',
            'password_confirmation' => 'ParishPassword123',
        ];

        return [
            'missing name' => [[...$valid, 'name' => '   '], 'name'],
            'name too short' => [[...$valid, 'name' => 'A'], 'name'],
            'name too long' => [[...$valid, 'name' => str_repeat('a', 101)], 'name'],
            'name with digits' => [[...$valid, 'name' => 'Member 123'], 'name'],
            'name with symbols' => [[...$valid, 'name' => '<script>'], 'name'],
            'missing email' => [[...$valid, 'email' => ''], 'email'],
            'invalid email' => [[...$valid, 'email' => 'not-an-email'], 'email'],
            'missing mobile' => [[...$valid, 'mobile' => ''], 'mobile'],
            'mobile not starting with 9' => [[...$valid, 'mobile' => '8171234567'], 'mobile'],
            'mobile too short' => [[...$valid, 'mobile' => '917123456'], 'mobile'],
            'mobile too long' => [[...$valid, 'mobile' => '91712345678'], 'mobile'],
            'mobile with letters' => [[...$valid, 'mobile' => '917abc4567'], 'mobile'],
            'missing password' => [[...$valid, 'password' => '', 'password_confirmation' => ''], 'password'],
            'password too short' => [[...$valid, 'password' => 'Abc123', 'password_confirmation' => 'Abc123'], 'password'],
            'password without uppercase' => [[...$valid, 'password' => 'parishpassword123', 'password_confirmation' => 'parishpassword123'], 'password'],
            'password without lowercase' => [[...$valid, 'password' => 'PARISHPASSWORD123', 'password_confirmation' => 'PARISHPASSWORD123'], 'password'],
            'password without number' => [[...$valid, 'password' => 'ParishPassword', 'password_confirmation' => 'ParishPassword'], 'password'],
            'password too long' => [[...$valid, 'password' => 'Aa1'.str_repeat('x', 70), 'password_confirmation' => 'Aa1'.str_repeat('x', 70)], 'password'],
            'password confirmation mismatch' => [[...$valid, 'password_confirmation' => 'ParishPassword124'], 'password'],
        ];
    }

    /**
     * @dataProvider invalidRegistrations
     *
     * @param  array<string, string>  $payload
     */
    public function test_registration_rejects_invalid_input(array $payload, string $field): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), $payload)
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors($field);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_duplicate_mobile_in_any_format(): void
    {
        User::factory()->create(['mobile' => '9171234567']);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Parish Member',
                'email' => 'new-member@example.com',
                'mobile' => '0917 123 4567',
                'password' => 'ParishPassword123',
                'password_confirmation' => 'ParishPassword123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['mobile' => 'This mobile number is already registered to another account.']);
    }

    public function test_registration_cannot_assign_itself_the_administrator_role(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Parish Member',
            'email' => 'member@example.com',
            'mobile' => '9171234567',
            'password' => 'ParishPassword123',
            'password_confirmation' => 'ParishPassword123',
            'role' => User::ROLE_ADMINISTRATOR,
        ])->assertRedirect(route('dashboard'));

        $this->assertSame(User::ROLE_PARISHIONER, User::firstOrFail()->role);
    }

    public function test_registration_form_keeps_input_and_shows_errors_after_failed_submit(): void
    {
        $this->followingRedirects()
            ->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Parish Member',
                'email' => 'member@example.com',
                'mobile' => '123',
                'password' => 'ParishPassword123',
                'password_confirmation' => 'ParishPassword123',
            ])
            ->assertOk()
            ->assertSee('Please fix the highlighted field below and try again.')
            ->assertSee('Please enter a valid Philippine mobile number, like 9171234567 or 09171234567.')
            ->assertSee('value="Parish Member"', false)
            ->assertSee('value="member@example.com"', false)
            ->assertDontSee('value="ParishPassword123"', false);
    }

    public function test_registration_page_includes_password_helpers(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('data-password-toggle="password_confirmation"', false)
            ->assertSee('data-password-rules', false)
            ->assertSee('At least 8 characters');
    }

    public function test_signed_in_users_cannot_open_registration(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('register'))
            ->assertRedirect();
    }

    public function test_registration_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post(route('register.store'), ['name' => ''])->assertRedirect();
        }

        $this->post(route('register.store'), ['name' => ''])->assertStatus(429);
    }
}
