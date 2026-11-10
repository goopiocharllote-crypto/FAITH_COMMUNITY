<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Faith Community Platform')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="min-h-screen md:flex">
        {!! view()->file(resource_path('users/navbar.blade.php'))->render() !!}

        <main class="min-w-0 flex-1">
            <header class="flex items-center justify-between border-b border-slate-200 bg-white px-5 py-4 sm:px-8">
                <h1 class="text-lg font-bold text-slate-800">@yield('page-title', 'Home')</h1>
                <span class="hidden text-sm text-slate-600 sm:inline">Welcome, {{ auth()->user()->name }}</span>
            </header>

            <div class="p-5 sm:p-8">
                @if (session('status'))
                    <div role="status" aria-live="polite" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                        {{ session('status') }}
                    </div>
                @endif

                @error('payment')
                    <div role="alert" class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
                        {{ $message }}
                    </div>
                @enderror

                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
