<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enter Code | Faith Community Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 px-3 py-4 sm:px-6 sm:py-8 lg:py-12">
    <main class="mx-auto flex min-h-[calc(100svh-2rem)] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl shadow-blue-950/10 sm:rounded-3xl md:min-h-[min(760px,calc(100vh-4rem))] md:flex-row">
        <aside
            class="relative flex min-h-[230px] w-full flex-col justify-between overflow-hidden bg-cover bg-center px-5 py-5 text-white sm:min-h-[280px] sm:px-12 sm:py-8 md:min-h-0 md:w-5/12 md:py-10"
            style="background-image: linear-gradient(145deg, rgb(8 47 73 / 94%), rgb(30 64 95 / 76%), rgb(15 23 42 / 78%)), url('{{ asset('images/BG_Image.png') }}')"
        >
            <div class="relative z-10">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-100 transition hover:text-white">
                    <x-heroicon-o-arrow-left class="h-4 w-4" />
                    Back to sign in
                </a>
            </div>
            <div class="relative z-10 py-4 text-center sm:py-6 md:py-0">
                <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-white p-1.5 shadow-xl shadow-slate-950/20 sm:mb-7 sm:h-36 sm:w-36 sm:rounded-3xl sm:p-3 md:h-44 md:w-44">
                    <img src="{{ asset('images/logo.png') }}" alt="Faith Community Loboc Parish logo" class="h-full w-full object-contain">
                </div>
                <h1 class="text-2xl font-bold tracking-tight sm:text-4xl">Faith Community</h1>
            </div>
            <p class="relative z-10 hidden text-center text-xs text-blue-100/80 md:block">Faith • Love • Believe • Belong</p>
        </aside>

        <section class="flex w-full flex-col justify-center px-5 py-8 sm:px-10 sm:py-10 md:w-7/12 md:px-12 md:py-12 lg:px-16">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-8">
                    <p class="mb-2 text-sm font-semibold uppercase tracking-[0.16em] text-blue-700">Account recovery</p>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Enter your code</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Type the 6-digit code we texted you, then choose a new password (8+ characters with upper and lower case letters and a number).</p>
                </div>

                @if (session('status'))
                    <div role="status" class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.code.update') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="mobile" class="mb-2 block text-sm font-semibold text-slate-700">Mobile number</label>
                        <input id="mobile" type="tel" name="mobile" value="{{ old('mobile', $mobile) }}" required autocomplete="tel" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3.5 text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div>
                        <label for="code" class="mb-2 block text-sm font-semibold text-slate-700">6-digit code</label>
                        <input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus autocomplete="one-time-code" placeholder="123456" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3.5 text-lg tracking-[0.4em] text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">New password</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3.5 text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirm new password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3.5 text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-900 px-5 py-3.5 font-semibold text-white shadow-lg shadow-blue-900/20 transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200">Reset password</button>
                </form>

                <p class="mt-7 text-center text-sm text-slate-600">
                    <a href="{{ route('login') }}" class="font-semibold text-blue-700 transition hover:text-blue-900 hover:underline">Back to sign in</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
