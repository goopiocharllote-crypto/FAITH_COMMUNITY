<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin | Faith Community Platform')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="min-h-screen md:flex">
        {!! view()->file(resource_path('admin/sidebar.blade.php'))->render() !!}

        <main class="min-w-0 flex-1">
            <header class="border-b border-slate-200 bg-white px-5 py-5 sm:px-8">
                @yield('header-content')
                @hasSection('header-content')
                @else
                    <h1 class="text-2xl font-bold text-slate-900">@yield('page-title', 'Admin')</h1>
                    @hasSection('page-description')
                        <p class="mt-1 text-sm text-slate-600">@yield('page-description')</p>
                    @endif
                @endif
            </header>
            <div class="p-5 sm:p-8">
                @if (session('status'))
                    <div role="status" aria-live="polite" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                        {{ session('status') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
