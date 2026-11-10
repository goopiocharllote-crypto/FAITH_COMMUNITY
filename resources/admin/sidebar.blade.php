<aside class="bg-slate-950 text-white md:sticky md:top-0 md:flex md:h-screen md:w-64 md:shrink-0 md:flex-col">
    <div class="border-b border-white/10 px-5 py-5">
        <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold tracking-tight">
            Faith Community
        </a>
        <p class="mt-1 text-sm text-slate-400">Administrator portal</p>
    </div>

    <nav aria-label="Administrator navigation" class="flex gap-2 overflow-x-auto px-3 py-3 md:flex-1 md:flex-col md:overflow-y-auto">
        @foreach ([
            ['Dashboard', 'admin.dashboard'],
            ['Announcements', 'admin.announcements'],
            ['Mass Intentions', 'admin.mass-intentions'],
            ['Schedules', 'admin.schedules'],
            ['Prayer Requests', 'admin.prayer-requests'],
            ['Donations', 'admin.donations'],
            ['Reports', 'admin.reports'],
            ['Print List', 'admin.printlist'],
            ['User Activity', 'admin.user-activity'],
            ['Settings', 'admin.settings'],
        ] as [$label, $routeName])
            <a
                href="{{ route($routeName) }}"
                @if (request()->routeIs($routeName)) aria-current="page" @endif
                class="{{ request()->routeIs($routeName) ? 'bg-blue-700 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }} shrink-0 rounded-lg px-4 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-blue-300 md:shrink"
            >
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <div class="flex items-center justify-between gap-4 border-t border-white/10 px-4 py-3 md:hidden">
        <p class="truncate text-sm text-slate-300">{{ auth()->user()->name }}</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="rounded-lg border border-slate-700 px-3 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-blue-300">
                Log out
            </button>
        </form>
    </div>

    <div class="hidden border-t border-white/10 p-4 md:block">
        <p class="mb-3 truncate text-sm text-slate-300">{{ auth()->user()->name }}</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full rounded-lg border border-slate-700 px-4 py-2.5 text-left text-sm font-medium text-slate-200 transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-blue-300">
                Log out
            </button>
        </form>
    </div>
</aside>
