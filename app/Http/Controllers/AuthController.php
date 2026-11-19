<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\LoginLog;
use App\Notifications\EmailVerificationCode;
use App\Notifications\PasswordResetCode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Facades\Socialite;
use Throwable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    /** One box: an email address gets a reset link, a mobile number gets a 6-digit code by text. */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $login = trim((string) $request->input('login', $request->input('email')));

        if ($login === '') {
            throw ValidationException::withMessages(['login' => __('Enter your email address or mobile number.')]);
        }

        if (str_contains($login, '@')) {
            $email = mb_strtolower($login);

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages(['login' => __('Enter a valid email address or mobile number.')]);
            }

            Password::sendResetLink(['email' => $email]);

            // Same response whether or not the account exists, so registered emails aren't revealed.
            return back()->with('status', __('If an account exists for that email, we have sent a password reset link. Please check your inbox.'));
        }

        $mobile = RegisterRequest::normalizeMobile($login);

        if (! preg_match('/^9[0-9]{9}$/', $mobile)) {
            throw ValidationException::withMessages(['login' => __('Enter a valid email address or a Philippine mobile number, like 09171234567.')]);
        }

        $this->sendResetCode($mobile);

        // Same response whether or not a number is registered.
        return redirect()->route('password.code', ['mobile' => $mobile])
            ->with('status', __('If that mobile number is registered, we have texted you a 6-digit code. It is valid for :minutes minutes.', ['minutes' => self::CODE_MINUTES]));
    }

    private const CODE_MINUTES = 10;

    private function sendResetCode(string $mobile): void
    {
        $user = User::query()->where('mobile', $mobile)->first();

        if (! $user) {
            return;
        }

        $existing = DB::table('password_reset_codes')->where('mobile', $mobile)->first();

        // One text per minute per number.
        if ($existing && now()->subSeconds(60)->lt(\Illuminate\Support\Carbon::parse($existing->created_at))) {
            return;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_codes')->updateOrInsert(['mobile' => $mobile], [
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::CODE_MINUTES),
            'created_at' => now(),
        ]);

        $user->notify(new PasswordResetCode($code, self::CODE_MINUTES));
    }

    public function showResetCode(Request $request): View
    {
        return view()->file(resource_path('auth/reset-code.blade.php'), [
            'mobile' => $request->query('mobile'),
        ]);
    }

    public function resetWithCode(Request $request): RedirectResponse
    {
        $request->merge(['mobile' => RegisterRequest::normalizeMobile((string) $request->input('mobile'))]);

        $request->validate([
            'mobile' => ['required', 'regex:/^9[0-9]{9}$/'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'confirmed', 'max:72', PasswordRule::min(8)->letters()->mixedCase()->numbers()],
        ], [
            'password.confirmed' => __('The passwords do not match.'),
            'code.digits' => __('The code is 6 digits.'),
        ]);

        $invalid = fn () => back()->withInput($request->only('mobile'))
            ->withErrors(['code' => __('That code is invalid or has expired. Please request a new one.')]);

        $row = DB::table('password_reset_codes')->where('mobile', $request->input('mobile'))->first();

        if (! $row || now()->greaterThan(\Illuminate\Support\Carbon::parse($row->expires_at)) || $row->attempts >= 5) {
            return $invalid();
        }

        if (! Hash::check($request->input('code'), $row->code_hash)) {
            DB::table('password_reset_codes')->where('mobile', $row->mobile)->increment('attempts');

            return $invalid();
        }

        $user = User::query()->where('mobile', $request->input('mobile'))->first();

        if (! $user) {
            return $invalid();
        }

        $user->forceFill(['password' => $request->input('password'), 'remember_token' => Str::random(60)])->save();
        DB::table('password_reset_codes')->where('mobile', $row->mobile)->delete();
        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', __('Your password has been reset. You can now sign in.'));
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

        if (! config('auth.verify_registration_email')) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'mobile' => $validated['mobile'],
                'password' => $validated['password'],
                'role' => User::ROLE_PARISHIONER,
            ]);

            return $this->finishRegistration($request, $user);
        }

        // The account is only created once the emailed code is confirmed, so a made-up email can't get in.
        $payload = Crypt::encryptString(json_encode([
            'name' => $validated['name'],
            'mobile' => $validated['mobile'],
            'password' => Hash::make($validated['password']),
        ]));

        if (! $this->sendEmailCode($validated['email'], $payload)) {
            return back()->withInput($request->only('name', 'email', 'mobile'))->withErrors([
                'email' => __('We could not send the verification email right now. Please check the address and try again.'),
            ]);
        }

        return redirect()->route('verification.notice', ['email' => $validated['email']])
            ->with('status', __('We emailed a 6-digit code to :email. Enter it below to finish creating your account.', ['email' => $validated['email']]));
    }

    private const EMAIL_CODE_MINUTES = 10;

    /** Creates a fresh code for the pending sign-up and emails it. Returns false if the email could not be sent. */
    private function sendEmailCode(string $email, string $payload): bool
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('email_verifications')->updateOrInsert(['email' => $email], [
            'payload' => $payload,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::EMAIL_CODE_MINUTES),
            'created_at' => now(),
        ]);

        try {
            Notification::route('mail', $email)->notify(new EmailVerificationCode($code, self::EMAIL_CODE_MINUTES));
        } catch (Throwable $exception) {
            Log::error('Verification email failed', ['email' => $email, 'error' => $exception->getMessage()]);
            DB::table('email_verifications')->where('email', $email)->delete();

            return false;
        }

        return true;
    }

    private function finishRegistration(Request $request, User $user): RedirectResponse
    {
        event(new Registered($user));
        $this->signIn($request, $user);

        return redirect()
            ->intended(route('dashboard'))
            ->with('newUser', true)
            ->with('status', __('Your account has been created successfully. You are now signed in.'));
    }

    /** Logs the user in and records the login for the User Activity page. */
    private function signIn(Request $request, User $user): void
    {
        Auth::login($user);
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now(), 'last_activity_at' => now()])->saveQuietly();
        $user->hasMany(LoginLog::class)->create(['login_at' => now(), 'ip_address' => $request->ip()]);
    }

    public function showVerifyEmail(Request $request): View
    {
        return view()->file(resource_path('auth/verify-email.blade.php'), [
            'email' => $request->query('email'),
        ]);
    }

    public function verifyEmail(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ], ['code.digits' => __('The code is 6 digits.')]);

        $invalid = fn () => back()->withInput($request->only('email'))
            ->withErrors(['code' => __('That code is invalid or has expired. Please request a new one.')]);

        $row = DB::table('email_verifications')->where('email', $request->input('email'))->first();

        if (! $row || now()->greaterThan(Carbon::parse($row->expires_at)) || $row->attempts >= 5) {
            return $invalid();
        }

        if (! Hash::check($request->input('code'), $row->code_hash)) {
            DB::table('email_verifications')->where('email', $row->email)->increment('attempts');

            return $invalid();
        }

        $data = json_decode(Crypt::decryptString($row->payload), true);

        // Someone may have taken this email or number while the code was pending.
        if (User::query()->where('email', $row->email)->orWhere('mobile', $data['mobile'])->exists()) {
            DB::table('email_verifications')->where('email', $row->email)->delete();

            return redirect()->route('register')->withErrors([
                'email' => __('This email or mobile number is already registered. Please sign in instead.'),
            ]);
        }

        $user = new User([
            'name' => $data['name'],
            'email' => $row->email,
            'mobile' => $data['mobile'],
            'password' => $data['password'],
            'role' => User::ROLE_PARISHIONER,
        ]);
        $user->email_verified_at = now();
        $user->save();

        DB::table('email_verifications')->where('email', $row->email)->delete();

        return $this->finishRegistration($request, $user);
    }

    public function resendEmailCode(Request $request): RedirectResponse
    {
        $email = mb_strtolower(trim((string) $request->input('email')));
        $row = DB::table('email_verifications')->where('email', $email)->first();

        // One email per minute; the reply is the same either way.
        if ($row && now()->subSeconds(60)->gte(Carbon::parse($row->created_at))) {
            $this->sendEmailCode($email, $row->payload);
        }

        return back()->with('status', __('If a sign-up is waiting for :email, we sent a new code. Please check your inbox.', ['email' => $email]));
    }

    public function redirectToGoogle(): RedirectResponse
    {
        abort_unless(config('services.google.client_id'), 404);

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        abort_unless(config('services.google.client_id'), 404);

        $fail = fn (string $message) => redirect()->route('login')->withErrors(['email' => $message]);

        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable) {
            return $fail(__('Google sign-in was cancelled or failed. Please try again.'));
        }

        $email = mb_strtolower((string) $google->getEmail());

        // Only trust an email address that Google itself has verified.
        if ($email === '' || ! filter_var($google->user['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return $fail(__('Your Google account does not have a verified email address.'));
        }

        $user = User::query()->where('email', $email)->first();

        if ($user && $user->role !== User::ROLE_PARISHIONER) {
            return $fail(__('Administrators must sign in with their email and password.'));
        }

        $isNew = $user === null;

        if ($isNew) {
            $user = new User([
                'name' => $google->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Str::random(40),
                'role' => User::ROLE_PARISHIONER,
            ]);
            $user->email_verified_at = now();
            $user->save();
        } elseif (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->saveQuietly();
        }

        $this->signIn($request, $user);

        $redirect = redirect()->intended(route('dashboard'));

        return $isNew
            ? $redirect->with('newUser', true)->with('status', __('Your account has been created with Google. You are now signed in.'))
            : $redirect;
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($user = $request->user()) {
            \App\Models\LoginLog::query()
                ->where('user_id', $user->id)
                ->whereNull('logout_at')
                ->update(['logout_at' => now()]);
            // Keep a real "last seen" time, dated just outside the 2-minute window so they show as Offline at once.
            $user->forceFill(['last_activity_at' => now()->subMinutes(3)])->saveQuietly();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
