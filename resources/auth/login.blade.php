<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Faith Community Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 px-3 py-4 sm:px-6 sm:py-8 lg:py-12">
    <main class="mx-auto flex min-h-[calc(100svh-2rem)] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl shadow-blue-950/10 sm:rounded-3xl md:min-h-[min(760px,calc(100vh-4rem))] md:flex-row">
        <aside
            class="relative flex min-h-[230px] w-full flex-col justify-between overflow-hidden bg-cover bg-center px-5 py-5 text-white sm:min-h-[280px] sm:px-12 sm:py-8 md:min-h-0 md:w-5/12 md:py-10"
            style="background-image: linear-gradient(145deg, rgb(8 47 73 / 94%), rgb(30 64 95 / 76%), rgb(15 23 42 / 78%)), url('{{ asset('images/BG_Image.png') }}')"
        >
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/35 to-transparent" aria-hidden="true"></div>

            <div class="relative z-10">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-100 transition hover:text-white">
                    <x-heroicon-o-arrow-left class="h-4 w-4" />
                    Back to home
                </a>
            </div>

            <div class="relative z-10 py-4 text-center sm:py-6 md:py-0">
                <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-white p-1.5 shadow-xl shadow-slate-950/20 sm:mb-7 sm:h-36 sm:w-36 sm:rounded-3xl sm:p-3 md:h-44 md:w-44">
                    <img src="{{ asset('images/logo.png') }}" alt="Faith Community Loboc Parish logo" class="h-full w-full object-contain">
                </div>
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-amber-200 sm:mb-3 sm:text-xs sm:tracking-[0.24em]">Parish of Saint Peter the Apostle</p>
                <h1 class="text-2xl font-bold tracking-tight sm:text-4xl">Faith Community</h1>
                <p class="mx-auto mt-2 max-w-sm text-xs leading-5 text-blue-100 sm:mt-3 sm:text-base sm:leading-6">A place to stay connected with your parish community in Loboc, Bohol.</p>
            </div>

            <p class="relative z-10 hidden text-center text-xs text-blue-100/80 md:block">Faith • Love • Believe • Belong</p>
        </aside>

        <section class="flex w-full flex-col justify-center px-5 py-8 sm:px-10 sm:py-10 md:w-7/12 md:px-12 md:py-12 lg:px-16">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-8">
                    <div class="mb-5 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-800">
                        <x-heroicon-o-lock-closed class="h-6 w-6" />
                    </div>
                    <p class="mb-2 text-sm font-semibold uppercase tracking-[0.16em] text-blue-700">Welcome back</p>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Sign in to your account</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Enter your details below to continue.</p>
                </div>

                @if ($errors->any())
                    <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" autocomplete="on" class="space-y-5">
                    @csrf

                    <div>
                        <label for="role" class="mb-2 block text-sm font-semibold text-slate-700">Sign in as</label>
                        <select id="role" name="role" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3.5 text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                            <option value="parishioner" @selected(old('role', 'parishioner') === 'parishioner')>Parishioner</option>
                            <option value="administrator" @selected(old('role') === 'administrator')>Administrator</option>
                        </select>
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email address</label>
                        <div class="relative">
                            <x-heroicon-o-envelope class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com" class="w-full rounded-xl border border-slate-300 bg-white py-3.5 pl-12 pr-4 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-blue-700 transition hover:text-blue-900 hover:underline">Forgot password?</a>
                            @endif
                        </div>
                        <div class="relative">
                            <x-heroicon-o-lock-closed class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" class="w-full rounded-xl border border-slate-300 bg-white py-3.5 pl-12 pr-12 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                            <button type="button" data-password-toggle aria-label="Show password" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <span data-password-icon-show><x-heroicon-o-eye class="h-5 w-5" /></span>
                                <span data-password-icon-hide class="hidden"><x-heroicon-o-eye-slash class="h-5 w-5" /></span>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-900 px-5 py-3.5 font-semibold text-white shadow-lg shadow-blue-900/20 transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200">
                        Sign in
                        <x-heroicon-o-arrow-right class="h-5 w-5" />
                    </button>
                </form>

                <p class="mt-7 text-center text-sm text-slate-600">
                    Don’t have an account?
                    <a href="{{ route('register') }}" class="font-semibold text-blue-700 transition hover:text-blue-900 hover:underline">Create an account</a>
                </p>
            </div>
        </section>
    </main>

    <footer class="mx-auto mt-6 max-w-5xl text-center text-xs text-slate-500">
        © {{ date('Y') }} Faith Community Platform · Parish of Loboc
    </footer>
</body>
</html>
