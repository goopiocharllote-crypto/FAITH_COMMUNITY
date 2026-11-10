@extends('layouts.landing')

@section('title', 'Faith Community Loboc Parish')

@section('page')
<div class="relative w-screen h-screen min-h-screen overflow-hidden bg-[#fffaf0]">

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

    <header class="absolute top-0 left-0 right-0 z-40 px-4 md:px-8 pt-5 md:pt-6">

        <nav class="max-w-[1400px] mx-auto bg-white rounded-2xl nav-shadow px-5 lg:px-9 py-3">

            <div class="flex items-center justify-between gap-5">

                <!-- LOGO -->

                <a
                    href="{{ route('landing') }}"
                    class="flex items-center gap-3 shrink-0"
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

                <div class="desktop-nav flex items-center gap-6 lg:gap-8 text-sm lg:text-[15px] font-semibold">

                    <a
                        href="{{ route('landing') }}"
                        class="
                            text-[#102d50]
                            border-b-2
                            border-[#d8ab31]
                            pb-2
                        "
                    >
                        Home
                    </a>

                    <a
                        href="{{ route('landing.about') }}"
                        class="
                            text-[#102d50]
                            hover:text-[#c29419]
                            transition
                        "
                    >
                        About
                    </a>

                    <a
                        href="{{ route('landing.services') }}"
                        class="
                            text-[#102d50]
                            hover:text-[#c29419]
                            transition
                        "
                    >
                        Services
                    </a>



                    <a
                        href="{{ route('landing.contact') }}"
                        class="
                            text-[#102d50]
                            hover:text-[#c29419]
                            transition
                        "
                    >
                        Contact
                    </a>

                </div>


                <!-- AUTH BUTTONS -->

                <div class="flex items-center gap-3 shrink-0">
                    <span class="hidden text-sm font-semibold text-[#102d50] md:inline">Welcome, {{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-xl bg-[#123d67] px-5 py-2.5 font-semibold text-white transition hover:bg-[#0d3154] md:px-8">
                            Log Out
                        </button>
                    </form>
                </div>

            </div>

        </nav>

    </header>


    <!-- ===================================================== -->
    <!-- HERO CONTENT -->
    <!-- ===================================================== -->

    <main
        class="
            relative
            z-10
            h-screen
            max-w-[1400px]
            mx-auto
            px-8
            lg:px-14
            pt-32
            pb-3
            flex
            flex-col
            overflow-hidden
        "
    >

        @if (session('status'))
            <div role="status" aria-live="polite" class="relative z-10 mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        <!-- MAIN HERO AREA -->

        <div class="flex-1 min-h-0 flex items-center">

            <div class="w-full flex items-center justify-between gap-10">

                <!-- HERO TEXT -->

                <div class="max-w-[620px]">

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

                    <div class="flex gap-3 mt-5">

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
                            href="{{ route('landing.about') }}"
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

        <section class="grid grid-cols-5 gap-4 shrink-0" aria-label="Parish services">

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




        </section>


        <!-- ===================================================== -->
        <!-- FOOTER COPYRIGHT -->
        <!-- ===================================================== -->

        <div class="pt-3 text-center text-[11px] text-[#102d50]/75 shrink-0">
            © {{ date('Y') }} Faith Community Loboc Parish.
            All rights reserved.
        </div>

    </main>

</div>
@endsection
