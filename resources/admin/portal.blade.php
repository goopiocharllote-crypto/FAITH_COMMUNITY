<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administrator Dashboard | Faith Community Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 px-4 py-10">
    <main class="mx-auto max-w-4xl rounded-2xl bg-white p-8 shadow-xl sm:p-12">
        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-center">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Administrator</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Welcome, {{ auth()->user()->name }}</h1>
                <p class="mt-2 text-slate-600">You are signed in to the administrator account.</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-xl bg-blue-900 px-5 py-3 font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200">
                    Log out
                </button>
            </form>
        </div>
    </main>
</body>
</html>
