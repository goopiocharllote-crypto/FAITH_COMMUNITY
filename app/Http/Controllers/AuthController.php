<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return redirect()->route(
            $role === User::ROLE_ADMINISTRATOR ? 'admin.dashboard' : 'dashboard'
        );
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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
