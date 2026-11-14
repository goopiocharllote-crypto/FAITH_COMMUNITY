<aside class="print:hidden bg-slate-800 text-white md:flex md:min-h-screen md:w-64 md:flex-col">
    <div class="flex items-center justify-between gap-3 px-5 py-4">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="" class="h-11 w-11 shrink-0 rounded-lg bg-white object-contain p-1">
            <span class="leading-tight">
                <span class="block text-sm font-bold tracking-wide">FAITH COMMUNITY</span>
                <span class="block text-sm font-semibold text-slate-300">LOBOC PARISH</span>
            </span>
        </a>

        <button
            type="button"
            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-white transition hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white md:hidden"
            aria-controls="user-navigation"
            aria-expanded="false"
            aria-label="Open navigation menu"
            data-mobile-menu-toggle
        >
            <svg data-menu-open-icon xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg data-menu-close-icon xmlns="http://www.w3.org/2000/svg" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav id="user-navigation" data-mobile-menu data-mobile-menu-flex class="hidden flex-col gap-1 px-3 pb-3 md:flex md:flex-1 md:overflow-visible md:py-4" aria-label="User navigation">
        <a href="{{ route('dashboard') }}" @class(['whitespace-nowrap rounded-lg px-3 py-2.5 text-sm font-medium transition hover:bg-white/10', 'bg-white/15' => request()->routeIs('dashboard')])>
            Home
        </a>
        <a href="{{ route('mass-intentions.index') }}" @class(['whitespace-nowrap rounded-lg px-3 py-2.5 text-sm font-medium transition hover:bg-white/10', 'bg-white/15' => request()->routeIs('mass-intentions.index', 'mass-intentions.store')])>
            Mass Intentions
        </a>
        <a href="{{ route('mass-intentions.history') }}" @class(['whitespace-nowrap rounded-lg px-3 py-2.5 text-sm font-medium transition hover:bg-white/10', 'bg-white/15' => request()->routeIs('mass-intentions.history', 'mass-intentions.confirmation', 'payments.*')])>
            Transactions
        </a>
        <a href="{{ route('virtual-candle') }}" @class(['whitespace-nowrap rounded-lg px-3 py-2.5 text-sm font-medium transition hover:bg-white/10', 'bg-white/15' => request()->routeIs('virtual-candle')])>
            Virtual Candle
        </a>
        <a href="{{ route('users.announcements') }}" @class(['whitespace-nowrap rounded-lg px-3 py-2.5 text-sm font-medium transition hover:bg-white/10', 'bg-white/15' => request()->routeIs('users.announcements')])>
            Announcements
        </a>

        <a href="{{ route('users.live-mass') }}" @class(['inline-flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2.5 text-sm font-medium transition hover:bg-white/10', 'bg-white/15' => request()->routeIs('users.live-mass')])>
            Live Mass
        </a>
        <a href="{{ route('users.my-request') }}" @class(['whitespace-nowrap rounded-lg px-3 py-2.5 text-sm font-medium transition hover:bg-white/10', 'bg-white/15' => request()->routeIs('users.my-request')])>
            My Requests
        </a>
        <a href="{{ route('users.profile') }}" @class(['whitespace-nowrap rounded-lg px-3 py-2.5 text-sm font-medium transition hover:bg-white/10', 'bg-white/15' => request()->routeIs('users.profile')])>
            Profile
        </a>

        <div class="mt-2 border-t border-white/10 pt-4 md:mt-auto">
            <p class="mb-3 truncate px-3 text-sm text-slate-300">{{ auth()->user()->name }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-sm font-semibold text-white transition hover:bg-white/10">
                    Log out
                </button>
            </form>
        </div>
    </nav>
</aside>