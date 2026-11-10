<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | Faith Community Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 px-3 py-4 sm:px-6 sm:py-8 lg:py-12">
    <main class="mx-auto flex min-h-[calc(100svh-2rem)] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl shadow-blue-950/10 sm:rounded-3xl lg:min-h-[min(820px,calc(100vh-4rem))] lg:flex-row">
        <aside
            class="relative flex min-h-[230px] w-full flex-col justify-between overflow-hidden bg-cover bg-center px-5 py-5 text-white sm:min-h-[280px] sm:px-12 sm:py-8 lg:min-h-0 lg:w-5/12 lg:py-10"
            style="background-image: linear-gradient(145deg, rgb(8 47 73 / 94%), rgb(30 64 95 / 76%), rgb(15 23 42 / 78%)), url('{{ asset('images/BG_Image.png') }}')"
        >
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/35 to-transparent" aria-hidden="true"></div>

            <div class="relative z-10">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-100 transition hover:text-white">
                    <x-heroicon-o-arrow-left class="h-4 w-4" />
                    Back to home
                </a>
            </div>

            <div class="relative z-10 py-4 text-center sm:py-6 lg:py-0">
                <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-white p-1.5 shadow-xl shadow-slate-950/20 sm:mb-7 sm:h-36 sm:w-36 sm:rounded-3xl sm:p-3 lg:h-44 lg:w-44">
                    <img src="{{ asset('images/logo.png') }}" alt="Faith Community Loboc Parish logo" class="h-full w-full object-contain">
                </div>
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-amber-200 sm:mb-3 sm:text-xs sm:tracking-[0.24em]">Parish of Loboc</p>
                <h1 class="text-2xl font-bold tracking-tight sm:text-4xl">Faith Community</h1>
                <p class="mx-auto mt-2 max-w-sm text-xs leading-5 text-blue-100 sm:mt-3 sm:text-base sm:leading-6">Join your parish community and stay connected.</p>
            </div>

            <p class="relative z-10 hidden text-center text-xs text-blue-100/80 lg:block">Faith • Love • Believe • Belong</p>
        </aside>

        <section class="flex w-full flex-col justify-center px-5 py-8 sm:px-10 sm:py-10 lg:w-7/12 lg:px-14 lg:py-12">
            <div class="mx-auto w-full max-w-xl">
                <div class="mb-7">
                    <div class="mb-5 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-800">
                        <x-heroicon-o-user-plus class="h-6 w-6" />
                    </div>
                    <p class="mb-2 text-sm font-semibold uppercase tracking-[0.16em] text-blue-700">Get started</p>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Create your account</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Fill in your details to join the community.</p>
                </div>

                <form method="POST" action="{{ route('register.store') }}" autocomplete="on" class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Name</label>
                        <div class="relative">
                            <x-heroicon-o-user class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                            <x-text-input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Enter your name" class="block w-full rounded-xl border-slate-300 !bg-white py-3.5 pl-12 pr-4 !text-slate-900 shadow-sm outline-none transition placeholder:!text-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100" />
                        </div>
                        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email address</label>
                        <div class="relative">
                            <x-heroicon-o-envelope class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                            <x-text-input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com" class="block w-full rounded-xl border-slate-300 !bg-white py-3.5 pl-12 pr-4 !text-slate-900 shadow-sm outline-none transition placeholder:!text-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="mobile" class="mb-2 block text-sm font-semibold text-slate-700">Mobile number</label>
                        <div class="flex">
                            <span class="inline-flex items-center rounded-l-xl border border-r-0 border-slate-300 bg-slate-50 px-4 text-sm font-medium text-slate-600">+63</span>
                            <x-text-input id="mobile" type="text" name="mobile" value="{{ old('mobile') }}" required maxlength="10" inputmode="numeric" pattern="9[0-9]{9}" autocomplete="tel-national" placeholder="9XXXXXXXXX" class="block min-w-0 w-full rounded-l-none rounded-r-xl border-slate-300 !bg-white py-3.5 px-4 !text-slate-900 shadow-sm outline-none transition placeholder:!text-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100" />
                        </div>
                        <p class="mt-1.5 text-xs text-slate-500">Enter 10 digits, starting with 9. Example: 9171234567</p>
                        <x-input-error :messages="$errors->get('mobile')" class="mt-1.5" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                            <div class="relative">
                                <x-heroicon-o-lock-closed class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                                <x-text-input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Create a password" class="block w-full rounded-xl border-slate-300 !bg-white py-3.5 pl-12 pr-4 !text-slate-900 shadow-sm outline-none transition placeholder:!text-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100" />
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                        </div>

                        <div>
                            <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirm password</label>
                            <div class="relative">
                                <x-heroicon-o-lock-closed class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                                <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat password" class="block w-full rounded-xl border-slate-300 !bg-white py-3.5 pl-12 pr-4 !text-slate-900 shadow-sm outline-none transition placeholder:!text-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100" />
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
                        </div>
                    </div>

                    <button type="submit" class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-blue-900 px-5 py-3.5 font-semibold text-white shadow-lg shadow-blue-900/20 transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200">
                        Create account
                        <x-heroicon-o-arrow-right class="h-5 w-5" />
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-600">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-semibold text-blue-700 transition hover:text-blue-900 hover:underline">Sign in</a>
                </p>
            </div>
        </section>
    </main>

    <footer class="mx-auto mt-6 max-w-6xl text-center text-xs text-slate-500">
        © {{ date('Y') }} Faith Community Platform · Parish of Loboc
    </footer>
</body>
</html>
