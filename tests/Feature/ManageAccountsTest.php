<?php

namespace Tests\Feature;

use App\Models\MassIntention;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class ManageAccountsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
    }

    public function test_admin_can_list_and_search_accounts(): void
    {
        $admin = $this->admin();
        User::factory()->create(['name' => 'Maria Santos', 'email' => 'maria@example.com', 'mobile' => '9171234567']);
        User::factory()->create(['name' => 'Jose Cruz', 'email' => 'jose@example.com']);

        $this->actingAs($admin)->get(route('admin.accounts'))
            ->assertOk()
            ->assertSee('Manage Accounts')
            ->assertSee('Maria Santos')
            ->assertSee('09171234567')
            ->assertSee('Jose Cruz');

        $this->actingAs($admin)->get(route('admin.accounts', ['search' => 'maria']))
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertDontSee('Jose Cruz');

        $this->actingAs($admin)->get(route('admin.accounts', ['search' => '0917123']))
            ->assertSee('Maria Santos')
            ->assertDontSee('Jose Cruz');
    }

    public function test_parishioners_cannot_open_manage_accounts(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->get(route('admin.accounts'))->assertForbidden();
        $this->actingAs($user)->patch(route('admin.accounts.deactivate', $other))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.accounts.destroy', $other))->assertForbidden();
    }

    public function test_deactivated_user_cannot_log_in_and_can_be_reactivated(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['email' => 'maria@example.com']);

        $this->actingAs($admin)->patch(route('admin.accounts.deactivate', $user))->assertSessionHas('accounts_success');
        $this->assertNotNull($user->fresh()->deactivated_at);

        auth()->logout();
        $this->post(route('login.store'), ['role' => 'parishioner', 'email' => 'maria@example.com', 'password' => 'password'])
            ->assertSessionHasErrors(['email' => User::DEACTIVATED_MESSAGE]);
        $this->assertGuest();

        $this->actingAs($admin)->patch(route('admin.accounts.reactivate', $user));
        $this->assertNull($user->fresh()->deactivated_at);

        auth()->logout();
        $this->post(route('login.store'), ['role' => 'parishioner', 'email' => 'maria@example.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_signed_in_user_is_logged_out_after_deactivation(): void
    {
        $user = User::factory()->create(['deactivated_at' => now()]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_deactivated_user_cannot_sign_in_with_google_or_reset_password(): void
    {
        User::factory()->create(['email' => 'gina@gmail.com', 'mobile' => '9171234567', 'deactivated_at' => now()]);

        config(['services.google.client_id' => 'id', 'services.google.client_secret' => 'secret']);
        $googleUser = (new SocialiteUser)->map(['id' => '1', 'name' => 'Gina', 'email' => 'gina@gmail.com']);
        $googleUser->user = ['email_verified' => true];
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => User::DEACTIVATED_MESSAGE]);
        $this->assertGuest();

        $this->post(route('password.email'), ['login' => 'gina@gmail.com'])->assertSessionHas('status');
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'gina@gmail.com']);

        $this->post(route('password.email'), ['login' => '09171234567']);
        $this->assertDatabaseMissing('password_reset_codes', ['mobile' => '9171234567']);
    }

    public function test_admin_cannot_deactivate_or_delete_themselves_or_other_admins(): void
    {
        $admin = $this->admin();
        $otherAdmin = $this->admin();

        foreach ([$admin, $otherAdmin] as $target) {
            $this->actingAs($admin)->patch(route('admin.accounts.deactivate', $target))->assertSessionHasErrors('accounts');
            $this->actingAs($admin)->get(route('admin.accounts.delete', $target))->assertRedirect(route('admin.accounts'));
            $this->actingAs($admin)->delete(route('admin.accounts.destroy', $target), ['confirm_name' => $target->name])
                ->assertSessionHasErrors('accounts');
            $this->assertNull($target->fresh()->deactivated_at);
            $this->assertNotSoftDeleted($target);
        }
    }

    public function test_delete_requires_confirmation_and_keeps_parish_records(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['name' => 'Maria Santos', 'email' => 'maria@example.com', 'mobile' => '9171234567']);
        $intention = MassIntention::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin)->get(route('admin.accounts.delete', $user))
            ->assertOk()
            ->assertSee('Delete Maria Santos')
            ->assertSee('to confirm');

        $this->actingAs($admin)->delete(route('admin.accounts.destroy', $user), ['confirm_name' => 'wrong'])
            ->assertSessionHasErrors('confirm_name');
        $this->assertNotSoftDeleted($user);

        $this->actingAs($admin)->delete(route('admin.accounts.destroy', $user), ['confirm_name' => 'maria santos'])
            ->assertRedirect(route('admin.accounts'));

        $this->assertSoftDeleted($user);
        $deleted = User::withTrashed()->find($user->id);
        $this->assertNull($deleted->mobile);
        $this->assertNotSame('maria@example.com', $deleted->email);

        // Parish records stay, and still show who they came from.
        $this->assertModelExists($intention);
        $this->assertSame('Maria Santos', $intention->fresh()->user->name);

        // The email and number are free to register again.
        $this->assertFalse(User::query()->where('email', 'maria@example.com')->exists());

        $this->actingAs($admin)->get(route('admin.accounts'))->assertDontSee('maria@example.com');
    }
}
