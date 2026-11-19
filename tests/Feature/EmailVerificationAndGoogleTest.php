<?php

namespace Tests\Feature;

use App\Models\LoginLog;
use App\Models\User;
use App\Notifications\EmailVerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class EmailVerificationAndGoogleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['auth.verify_registration_email' => true]);
    }

    private function signup(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'mobile' => '9171234567',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ], $overrides);
    }

    private function sentCode(): string
    {
        $code = null;
        Notification::assertSentOnDemand(EmailVerificationCode::class, function ($n) use (&$code) {
            $code = $n->code;

            return true;
        });

        return $code;
    }

    public function test_signup_creates_no_account_until_the_emailed_code_is_confirmed(): void
    {
        Notification::fake();

        $this->post('/register', $this->signup())->assertRedirect('/verify-email?email=juan%40example.com');
        $this->assertDatabaseMissing('users', ['email' => 'juan@example.com']);
        $this->assertGuest();

        $code = $this->sentCode();
        $this->get('/verify-email?email=juan@example.com')->assertOk()->assertSee('Verify your email');

        $this->post('/verify-email', ['email' => 'juan@example.com', 'code' => $code])->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'juan@example.com')->firstOrFail();
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('9171234567', $user->mobile);
        $this->assertTrue(Hash::check('Secret123', $user->password));
        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, LoginLog::query()->where('user_id', $user->id)->count());
        $this->assertDatabaseMissing('email_verifications', ['email' => 'juan@example.com']);
    }

    public function test_a_wrong_code_is_rejected_and_five_misses_lock_the_code(): void
    {
        Notification::fake();
        $this->post('/register', $this->signup());
        $code = $this->sentCode();
        $wrong = $code === '000000' ? '111111' : '000000';

        for ($i = 0; $i < 5; $i++) {
            $this->post('/verify-email', ['email' => 'juan@example.com', 'code' => $wrong])->assertSessionHasErrors('code');
        }

        $this->post('/verify-email', ['email' => 'juan@example.com', 'code' => $code])->assertSessionHasErrors('code');
        $this->assertDatabaseMissing('users', ['email' => 'juan@example.com']);
    }

    public function test_an_expired_code_is_rejected(): void
    {
        Notification::fake();
        $this->post('/register', $this->signup());
        $code = $this->sentCode();
        DB::table('email_verifications')->update(['expires_at' => now()->subMinute()]);

        $this->post('/verify-email', ['email' => 'juan@example.com', 'code' => $code])->assertSessionHasErrors('code');
        $this->assertDatabaseMissing('users', ['email' => 'juan@example.com']);
    }

    public function test_resend_is_limited_to_one_email_a_minute(): void
    {
        Notification::fake();
        $this->post('/register', $this->signup());

        $this->post('/verify-email/resend', ['email' => 'juan@example.com'])->assertSessionHas('status');
        Notification::assertSentOnDemandTimes(EmailVerificationCode::class, 1);

        DB::table('email_verifications')->update(['created_at' => now()->subMinutes(2)]);
        $this->post('/verify-email/resend', ['email' => 'juan@example.com'])->assertSessionHas('status');
        Notification::assertSentOnDemandTimes(EmailVerificationCode::class, 2);
    }

    public function test_signup_still_validates_the_form_before_sending_any_email(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'juan@example.com']);

        $this->post('/register', $this->signup())->assertSessionHasErrors('email');
        Notification::assertNothingSent();
    }

    public function test_verification_can_be_switched_off(): void
    {
        config(['auth.verify_registration_email' => false]);

        $this->post('/register', $this->signup())->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    private function mockGoogle(string $email, bool $verified = true, string $name = 'Gina Google'): void
    {
        config(['services.google.client_id' => 'id', 'services.google.client_secret' => 'secret']);

        $googleUser = (new SocialiteUser)->map(['id' => '1', 'name' => $name, 'email' => $email]);
        $googleUser->user = ['email_verified' => $verified];

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($googleUser);
        $provider->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_the_google_button_only_shows_when_google_is_configured(): void
    {
        config(['services.google.client_id' => null]);
        $this->get('/login')->assertOk()->assertDontSee('Continue with Google');
        $this->get('/auth/google')->assertNotFound();

        config(['services.google.client_id' => 'id']);
        $this->get('/login')->assertSee('Continue with Google');
        $this->get('/register')->assertSee('Continue with Google');
    }

    public function test_google_creates_a_verified_parishioner_and_signs_them_in(): void
    {
        $this->mockGoogle('gina@gmail.com');

        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'gina@gmail.com')->firstOrFail();
        $this->assertSame(User::ROLE_PARISHIONER, $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->mobile);
        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, LoginLog::query()->where('user_id', $user->id)->count());
    }

    public function test_google_signs_in_an_existing_parishioner_without_duplicating_the_account(): void
    {
        $existing = User::factory()->create(['email' => 'gina@gmail.com', 'email_verified_at' => null]);
        $this->mockGoogle('gina@gmail.com');

        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $this->assertSame(1, User::query()->where('email', 'gina@gmail.com')->count());
        $this->assertAuthenticatedAs($existing);
        $this->assertNotNull($existing->fresh()->email_verified_at);
    }

    public function test_google_cannot_be_used_to_sign_in_an_administrator(): void
    {
        User::factory()->create(['email' => 'boss@gmail.com', 'role' => User::ROLE_ADMINISTRATOR]);
        $this->mockGoogle('boss@gmail.com');

        $this->get('/auth/google/callback')->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_google_accounts_with_an_unverified_email_are_refused(): void
    {
        $this->mockGoogle('gina@gmail.com', verified: false);

        $this->get('/auth/google/callback')->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'gina@gmail.com']);
    }
}
