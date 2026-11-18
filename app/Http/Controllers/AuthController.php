<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return response(view()->file(resource_path('auth/login.blade.php')))
            ->header('Cache-Control', 'no-store, private, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache');
    }

    public function showRegistration(): View
    {
        return view()->file(resource_path('auth/register.blade.php'));
    }

    public function login(Request $request): RedirectResponse
    {
        if (is_string($request->input('email'))) {
            // Emails are stored lowercase at registration.
            $request->merge(['email' => mb_strtolower(trim($request->input('email')))]);
        }

        $credentials = $request->validate([
            'role' => ['required', 'string', 'in:'.User::ROLE_PARISHIONER.','.User::ROLE_ADMINISTRATOR],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $role = $credentials['role'];

        $throttleKey = strtolower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => __('Too many login attempts. Please try again in :seconds seconds.', [
                    'seconds' => RateLimiter::availableIn($throttleKey),
                ]),
            ]);
        }

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $user = $request->user();
        $user->forceFill(['last_login_at' => now(), 'last_activity_at' => now()])->saveQuietly();
        $user->hasMany(\App\Models\LoginLog::class)->create([
            'login_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route(
            $role === User::ROLE_ADMINISTRATOR ? 'admin.dashboard' : 'dashboard'
        );
    }

    public function showForgotPassword(): View
    {
        return view()->file(resource_path('auth/forgot-password.blade.php'));
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $request->validate(['email' => ['required', 'string', 'email']]);

        Password::sendResetLink($request->only('email'));

        // Same response whether or not the account exists, so registered emails aren't revealed.
        return back()->with('status', __('If an account exists for that email, we have sent a password reset link. Please check your inbox.'));
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view()->file(resource_path('auth/reset-password.blade.php'), [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', 'max:72', PasswordRule::min(8)->letters()->mixedCase()->numbers()],
        ], ['password.confirmed' => __('The passwords do not match.')]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('This password reset link is invalid or has expired. Please request a new one.')]);
        }

        return redirect()->route('login')->with('status', __('Your password has been reset. You can now sign in.'));
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'password' => $validated['password'],
            'role' => User::ROLE_PARISHIONER,
        ]);

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->intended(route('dashboard'))
            ->with('newUser', true)
            ->with('status', __('Your account has been created successfully. You are now signed in.'));
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($user = $request->user()) {
            \App\Models\LoginLog::query()
                ->where('user_id', $user->id)
                ->whereNull('logout_at')
                ->update(['logout_at' => now()]);
            $user->forceFill(['last_activity_at' => null])->saveQuietly();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
