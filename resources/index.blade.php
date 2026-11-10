@extends('layouts.landing')

@section('title', 'Faith Community Loboc Parish')

@section('page')
<div class="landing-page relative w-screen min-h-screen bg-[#fffaf0]">

    <!-- ===================================================== -->
    <!-- HERO BACKGROUND -->
    <!-- ===================================================== -->

    <div
        class="
            fixed
            inset-0
            z-0
            w-screen
            h-screen
            bg-cover
            bg-center
            bg-no-repeat
        "
        style="
            background-image:
                linear-gradient(
                    90deg,
                    rgba(255,250,239,.96) 0%,
                    rgba(255,250,239,.82) 24%,
                    rgba(255,250,239,.48) 43%,
                    rgba(255,250,239,.14) 68%,
                    rgba(255,250,239,.02) 100%
                ),
                url('{{ asset('images/BG_Image.png') }}');
            background-size: cover;
            background-position: center center;
        "
    ></div>


    <!-- ===================================================== -->
    <!-- NAVBAR -->
    <!-- ===================================================== -->

    <header class="fixed top-0 left-0 right-0 z-40 px-4 md:px-8 pt-5 md:pt-6">

        <nav class="site-nav max-w-[1400px] mx-auto bg-white rounded-2xl nav-shadow px-5 lg:px-9 py-3">

            <div class="site-nav-row flex items-center justify-between gap-5">

                <!-- LOGO -->

                <a
                    href="{{ route('landing') }}#home"
                    class="site-logo flex items-center gap-3 shrink-0"
                >

                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="Faith Community Loboc Parish"
                        class="h-12 md:h-14 w-auto"
                    >

                    <div class="leading-tight">

                        <h1 class="font-bold text-lg md:text-xl tracking-wide text-[#102d50]">
                            FAITH COMMUNITY
                        </h1>

                        <p class="font-bold text-lg md:text-xl tracking-wide text-[#102d50]">
                            LOBOC PARISH
                        </p>

                    </div>

                </a>


                <!-- NAVIGATION -->

                <div class="site-nav-links desktop-nav flex items-center gap-6 lg:gap-8 text-sm lg:text-[15px] font-semibold">

                    <a href="{{ route('landing') }}#home" class="site-nav-link is-active" data-nav-link="home" aria-current="true">Home</a>
                    <a href="{{ route('landing') }}#about" class="site-nav-link" data-nav-link="about">About</a>
                    <a href="{{ route('landing') }}#services" class="site-nav-link" data-nav-link="services">Services</a>
                    <a href="{{ route('landing') }}#announcements" class="site-nav-link" data-nav-link="announcements">Announcements</a>
                    <a href="{{ route('landing') }}#contact" class="site-nav-link" data-nav-link="contact">Contact</a>

                </div>

                <button
                    type="button"
                    class="mobile-menu-toggle hidden items-center justify-center rounded-xl border border-[#102d50] p-2 text-[#102d50] hover:bg-[#fffaf0] focus:outline-none focus:ring-2 focus:ring-[#d8ab31]"
                    aria-label="Open navigation menu"
                    aria-expanded="false"
                    aria-controls="mobile-navigation"
                    data-mobile-menu-toggle
                >
                    <svg data-menu-open-icon xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg data-menu-close-icon xmlns="http://www.w3.org/2000/svg" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- AUTH BUTTONS -->

                <div class="site-auth-links flex items-center gap-3 shrink-0">
                    @auth
                        <span class="hidden text-sm font-semibold text-[#102d50] lg:inline">Welcome, {{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="site-auth-link rounded-xl bg-[#123d67] px-5 py-2.5 font-semibold text-white transition hover:bg-[#0d3154] md:px-8">
                                Log Out
                            </button>
                        </form>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="site-auth-link rounded-xl border border-[#102d50] px-5 py-2.5 font-semibold text-[#102d50] transition hover:bg-[#102d50] hover:text-white md:px-7"
                        >
                            Log In
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="site-auth-link rounded-xl bg-[#123d67] px-5 py-2.5 font-semibold text-white transition hover:bg-[#0d3154] md:px-8"
                        >
                            Register
                        </a>
                    @endauth
                </div>

            </div>

            <div id="mobile-navigation" class="mobile-menu" data-mobile-menu hidden>
                <nav class="mobile-menu-links" aria-label="Mobile navigation">
                    <a href="{{ route('landing') }}#home" class="is-active" data-nav-link="home" aria-current="true">Home</a>
                    <a href="{{ route('landing') }}#about" data-nav-link="about">About</a>
                    <a href="{{ route('landing') }}#services" data-nav-link="services">Services</a>
                    <a href="{{ route('landing') }}#announcements" data-nav-link="announcements">Announcements</a>
                    <a href="{{ route('landing') }}#contact" data-nav-link="contact">Contact</a>
                    <div class="mobile-menu-auth">
                        @auth
                            <span class="self-center text-sm font-semibold text-[#102d50]">Welcome, {{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="mobile-register">Log Out</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="mobile-login">Log In</a>
                            <a href="{{ route('register') }}" class="mobile-register">Register</a>
                        @endauth
                    </div>
                </nav>
            </div>

        </nav>

    </header>


    <!-- ===================================================== -->
    <!-- HERO CONTENT -->
    <!-- ===================================================== -->

    <main
        id="home"
        class="
            relative
            z-10
            landing-main
            min-h-screen
            max-w-[1400px]
            mx-auto
            px-8
            lg:px-14
            pt-32
            pb-3
            flex
            flex-col
            overflow-visible
        "
    >

        @if (session('status'))
            <div role="status" aria-live="polite" class="relative z-10 mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        <!-- MAIN HERO AREA -->

        <div class="landing-hero flex-1 min-h-0 flex items-center">

            <div class="landing-hero-row w-full flex items-center justify-between gap-10">

                <!-- HERO TEXT -->

                <div class="landing-copy max-w-[620px]">

                    <p
                        class="
                            text-sm
                            md:text-base
                            tracking-[0.32em]
                            font-semibold
                            text-[#755416]
                            mb-3
                        "
                    >
                        WELCOME TO
                    </p>


                    <h2
                        class="
                            hero-title
                            text-[3.5rem]
                            lg:text-[4.5rem]
                            leading-[.92]
                            font-bold
                            tracking-tight
                            text-[#102d50]
                        "
                    >
                        FAITH COMMUNITY
                        <br>
                        LOBOC PARISH
                    </h2>


                    <div class="w-12 h-[3px] bg-[#d8ab31] my-4"></div>


                    <p
                        class="
                            text-xl
                            md:text-2xl
                            italic
                            font-semibold
                            leading-tight
                            text-[#102d50]
                            max-w-[510px]
                        "
                    >
                        “Together in Faith,
                        <br>
                        Stronger in Community.”
                    </p>


                    <p
                        class="
                            mt-4
                            text-base
                            md:text-lg
                            leading-relaxed
                            text-gray-700
                            max-w-[500px]
                        "
                    >
                        A digital parish community, bringing us closer
                        in prayer, service, and love for God and one another.
                    </p>


                    <!-- HERO BUTTONS -->

                    <div class="landing-actions flex gap-3 mt-5">

                        <a
                            href="{{ route('register') }}"
                            class="
                                bg-[#123d67]
                                text-white
                                px-7
                                py-3
                                rounded-xl
                                font-bold
                                shadow-md
                                hover:bg-[#0d3154]
                                transition
                            "
                        >
                            Get Started
                            <span class="ml-2">→</span>
                        </a>


                        <a
                            href="{{ route('landing') }}#about"
                            class="
                                bg-white/90
                                border
                                border-[#123d67]
                                text-[#102d50]
                                px-7
                                py-3
                                rounded-xl
                                font-bold
                                hover:bg-white
                                transition
                            "
                        >
                            Learn More
                        </a>

                    </div>

                </div>


                <!-- VERSE CARD -->

                <div
                    class="
                        verse-card
                        soft-card
                        bg-white/75
                        backdrop-blur-sm
                        w-[245px]
                        rounded-2xl
                        px-7
                        py-6
                        text-center
                        shrink-0
                        mr-4
                    "
                >

                    <div class="text-5xl leading-none text-[#123d67] font-bold">
                        “
                    </div>

                    <p class="text-base italic leading-relaxed text-[#102d50] mt-1">
                        “For where two or three are gathered in my name,
                        there am I with them.”
                    </p>

                    <p class="text-sm font-semibold text-[#102d50] mt-4">
                        — Matthew 18:20
                    </p>

                </div>

            </div>

        </div>


        <!-- ===================================================== -->
        <!-- SERVICE CARDS -->
        <!-- ===================================================== -->

        <div class="landing-services grid grid-cols-5 gap-4 shrink-0">

            <!-- APPOINTMENT -->

            <a
                href="{{ route('mass-intentions.index') }}"
                class="
                    service-card
                    bg-white
                    rounded-2xl
                    px-4
                    py-4
                    text-center
                    border
                    border-gray-100
                "
            >

                <div
                    class="
                        w-14
                        h-14
                        mx-auto
                        rounded-full
                        bg-blue-100
                        flex
                        items-center
                        justify-center
                        text-2xl
                        mb-2
                    "
                >
                    📅
                </div>

                <h3 class="text-[16px] font-bold text-[#102d50]">
                    Book an Appointment
                </h3>

                <p class="mt-1 text-xs leading-relaxed text-gray-600">
                    Schedule your church appointments easily.
                </p>

            </a>


            <!-- REQUEST -->

            <a
                href="{{ route('prayer-requests.index') }}"
                class="
                    service-card
                    bg-white
                    rounded-2xl
                    px-4
                    py-4
                    text-center
                    border
                    border-gray-100
                "
            >

                <div
                    class="
                        w-14
                        h-14
                        mx-auto
                        rounded-full
                        bg-amber-50
                        flex
                        items-center
                        justify-center
                        text-2xl
                        mb-2
                    "
                >
                    📄
                </div>

                <h3 class="text-[16px] font-bold text-[#102d50]">
                    Make a Request
                </h3>

                <p class="mt-1 text-xs leading-relaxed text-gray-600">
                    Submit your sacramental and parish requests.
                </p>

            </a>


            <!-- VIRTUAL CANDLE -->

            <a
                href="{{ route('virtual-candle') }}"
                class="
                    service-card
                    bg-white
                    rounded-2xl
                    px-4
                    py-4
                    text-center
                    border
                    border-gray-100
                "
            >

                <div
                    class="
                        w-14
                        h-14
                        mx-auto
                        rounded-full
                        bg-amber-50
                        flex
                        items-center
                        justify-center
                        text-2xl
                        mb-2
                    "
                >
                    🕯️
                </div>

                <h3 class="text-[16px] font-bold text-[#102d50]">
                    Light a Virtual Candle
                </h3>

                <p class="mt-1 text-xs leading-relaxed text-gray-600">
                    Offer your prayers and intentions.
                </p>

            </a>


            <!-- DONATION -->

            <a
                href="{{ route('donations.index') }}"
                class="
                    service-card
                    bg-white
                    rounded-2xl
                    px-4
                    py-4
                    text-center
                    border
                    border-gray-100
                "
            >

                <div
                    class="
                        w-14
                        h-14
                        mx-auto
                        rounded-full
                        bg-emerald-50
                        flex
                        items-center
                        justify-center
                        text-2xl
                        mb-2
                    "
                >
                    💚
                </div>

                <h3 class="text-[16px] font-bold text-[#102d50]">
                    Make a Donation
                </h3>

                <p class="mt-1 text-xs leading-relaxed text-gray-600">
                    Support our parish missions and programs.
                </p>

            </a>




        </div>


    </main>

    <section id="services" class="landing-section relative z-10 bg-[#fffaf0]/95 px-6 py-20 md:px-10">
        <div class="mx-auto max-w-6xl">
            <div class="mb-8 text-center">
                <p class="text-sm font-semibold tracking-[0.3em] text-[#755416]">SERVICES</p>
                <h2 class="mt-2 text-4xl font-bold text-[#102d50] md:text-5xl">Our Parish Services</h2>
                <div class="mx-auto mt-4 h-[3px] w-12 bg-[#d8ab31]"></div>
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('mass-intentions.index') }}" class="service-card rounded-2xl bg-white p-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-2xl">📅</div>
                    <h3 class="mt-4 text-lg font-bold text-[#102d50]">Mass Intentions</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">Submit Mass intentions online and choose an available parish schedule.</p>
                </a>
                <a href="{{ route('prayer-requests.index') }}" class="service-card rounded-2xl bg-white p-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 text-2xl">🙏</div>
                    <h3 class="mt-4 text-lg font-bold text-[#102d50]">Prayer Requests</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">Send prayer requests and stay connected with parish prayer services.</p>
                </a>
                <a href="{{ route('donations.index') }}" class="service-card rounded-2xl bg-white p-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-2xl">💚</div>
                    <h3 class="mt-4 text-lg font-bold text-[#102d50]">Online Donations</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">Support parish missions, activities, and programs through donations.</p>
                </a>
                <a href="{{ route('landing') }}#announcements" class="service-card rounded-2xl bg-white p-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-rose-50 text-2xl">📣</div>
                    <h3 class="mt-4 text-lg font-bold text-[#102d50]">Announcements</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">Stay informed about parish news, activities, schedules, and events.</p>
                </a>
            </div>
        </div>
    </section>

    <section id="about" class="landing-section relative z-10 bg-white/95 px-6 py-20 md:px-10">
        <div class="mx-auto max-w-6xl">
            <div class="mb-8 text-center">
                <p class="text-sm font-semibold tracking-[0.3em] text-[#755416]">ABOUT US</p>
                <h2 class="mt-2 text-4xl font-bold text-[#102d50] md:text-5xl">About the Parish</h2>
                <div class="mx-auto mt-4 h-[3px] w-12 bg-[#d8ab31]"></div>
            </div>
            <div class="grid grid-cols-1 items-stretch gap-5 md:grid-cols-3">
                <div class="soft-card rounded-2xl bg-white p-7 md:col-span-2">
                    <h3 class="text-2xl font-bold text-[#102d50]">A Parish Family Walking Together in Faith</h3>
                    <p class="mt-4 leading-relaxed text-gray-700">The Faith Community Loboc Parish platform brings parishioners closer to the Church by providing convenient access to parish services, prayer, Mass intentions, donations, announcements, and community information.</p>
                    <p class="mt-4 leading-relaxed text-gray-700">Our goal is to support a connected parish community where faith, service, and love are strengthened through accessible digital services.</p>
                </div>
                <div class="soft-card flex flex-col justify-center rounded-2xl bg-[#12375c] p-7 text-white">
                    <p class="text-sm tracking-[0.25em] text-[#e6c65c]">OUR MESSAGE</p>
                    <p class="mt-4 text-2xl italic leading-relaxed">“Together in Faith, Stronger in Community.”</p>
                    <p class="mt-4 text-white/80">Parish of Saint Peter the Apostle — Loboc, Bohol</p>
                </div>
            </div>
        </div>
    </section>

    <section id="announcements" class="landing-section relative z-10 bg-[#fffaf0]/95 px-6 py-20 md:px-10">
        <div class="mx-auto max-w-6xl">
            <div class="mb-8 text-center">
                <p class="text-sm font-semibold tracking-[0.3em] text-[#755416]">STAY CONNECTED</p>
                <h2 class="mt-2 text-4xl font-bold text-[#102d50] md:text-5xl">Parish Announcements</h2>
                <div class="mx-auto mt-4 h-[3px] w-12 bg-[#d8ab31]"></div>
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                @forelse ($announcements as $announcement)
                    <article class="soft-card rounded-2xl bg-white p-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#755416]">
                            {{ optional($announcement->announcement_date ?? $announcement->created_at)->format('F d, Y') }}
                        </p>
                        <h3 class="mt-3 text-xl font-bold text-[#102d50]">{{ $announcement->title }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ \Illuminate\Support\Str::limit($announcement->content, 230) }}</p>
                    </article>
                @empty
                    <div class="soft-card flex items-center justify-center rounded-2xl bg-white p-10 text-center md:col-span-3">
                        <div>
                            <div class="text-5xl">📣</div>
                            <h3 class="mt-4 text-2xl font-bold text-[#102d50]">No announcements available yet.</h3>
                            <p class="mt-2 text-gray-600">Please check again later.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            @if (($livestreams ?? collect())->isNotEmpty())
                <div class="soft-card mt-8 rounded-2xl bg-white p-6">
                    <h3 class="flex items-center gap-2 text-xl font-bold text-[#102d50]">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-600" aria-hidden="true"></span>
                        Watch Mass Online
                    </h3>
                    <ul class="mt-4 divide-y divide-gray-100">
                        @foreach ($livestreams as $stream)
                            <li class="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-[#102d50]">{{ $stream->title }}</p>
                                    <p class="text-sm text-gray-600">{{ $stream->mass_date->format('l, F j · g:i A') }}</p>
                                </div>
                                <a href="{{ $stream->livestream_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-fit items-center rounded-xl bg-[#123d67] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0d3154]">
                                    {{ $stream->isLiveNow() ? 'Watch live now' : 'Livestream link' }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    <section id="contact" class="landing-section relative z-10 bg-white/95 px-6 py-20 md:px-10">
        <div class="mx-auto max-w-6xl">
            <div class="mb-8 text-center">
                <p class="text-sm font-semibold tracking-[0.3em] text-[#755416]">CONTACT US</p>
                <h2 class="mt-2 text-4xl font-bold text-[#102d50] md:text-5xl">Contact Information</h2>
                <div class="mx-auto mt-4 h-[3px] w-12 bg-[#d8ab31]"></div>
            </div>
            <div class="grid grid-cols-1 items-stretch gap-6 md:grid-cols-2">
                <div class="soft-card rounded-2xl bg-white p-8">
                    <h3 class="text-2xl font-bold text-[#102d50]">Get in Touch</h3>
                    <p class="mt-4 leading-relaxed text-gray-700">For parish concerns, service inquiries, and community information, you may contact the Parish Office through the details below.</p>
                    <div class="mt-6 space-y-4 text-gray-700">
                        <p><strong>📍 Location:</strong> Loboc, Bohol, Philippines</p>
                        <p><strong>✉ Email:</strong> parish@gmail.com</p>
                        <p><strong>☎ Phone:</strong> +63 XXX XXX XXXX</p>
                    </div>
                </div>
                <div class="soft-card flex flex-col justify-center rounded-2xl bg-[#12375c] p-8 text-white">
                    <p class="text-sm tracking-[0.25em] text-[#e6c65c]">PARISH OF SAINT PETER THE APOSTLE</p>
                    <h3 class="mt-3 text-3xl font-bold">Faith. Service. Love.</h3>
                    <p class="mt-4 leading-relaxed text-white/80">We are here to serve our parish community through faith, prayer, and love.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="relative z-10 bg-[#102d50] px-6 py-10 text-white md:px-10">
        <div class="mx-auto flex max-w-6xl flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('landing') }}#home" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="" class="h-12 w-auto">
                <span class="font-bold tracking-wide">FAITH COMMUNITY<br>LOBOC PARISH</span>
            </a>
            <nav class="flex flex-wrap gap-x-5 gap-y-2 text-sm text-white/85" aria-label="Footer navigation">
                <a href="{{ route('landing') }}#home" class="hover:text-[#e6c65c]">Home</a>
                <a href="{{ route('landing') }}#about" class="hover:text-[#e6c65c]">About</a>
                <a href="{{ route('landing') }}#services" class="hover:text-[#e6c65c]">Services</a>
                <a href="{{ route('landing') }}#announcements" class="hover:text-[#e6c65c]">Announcements</a>
                <a href="{{ route('landing') }}#contact" class="hover:text-[#e6c65c]">Contact</a>
            </nav>
        </div>
        <p class="mx-auto mt-8 max-w-6xl border-t border-white/20 pt-5 text-center text-xs text-white/70">
            © {{ date('Y') }} Faith Community Loboc Parish. All rights reserved.
        </p>
    </footer>

</div>w
@endsection
