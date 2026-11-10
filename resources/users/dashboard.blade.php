@extends('layouts.user')

@section('title', 'Home | Faith Community Platform')

@section('page-title', 'Home')

@section('content')

<!-- DASHBOARD CONTAINER -->
<div class="w-full min-w-0 overflow-x-hidden">

    <!-- WELCOME -->
    <header class="mb-6 sm:mb-7">

        @if ($newUser)
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight break-words">
                Welcome, {{ auth()->user()->name }}!
            </h2>

            <p class="text-gray-600 mt-2 text-sm sm:text-base">
                Welcome to Faith Community Platform.
            </p>
        @else
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight break-words">
                Welcome back, {{ auth()->user()->name }}!
            </h2>

            <p class="text-gray-600 mt-2 text-sm sm:text-base">
                Welcome back to Faith Community Platform.
            </p>
        @endif

    </header>


    <!-- SUMMARY CARDS -->
    <section
        class="grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4"
        aria-label="Account summary"
    >

        <!-- ACTIVE MASS INTENTION -->
        <div
            class="
                bg-purple-100
                border
                border-purple-300
                rounded-2xl
                p-4
                sm:p-5
                min-h-[130px]
                sm:min-h-[135px]
                flex
                flex-col
                justify-between
                min-w-0
            "
        >

            <div class="min-w-0">

                <p class="text-purple-600 font-semibold text-sm sm:text-base leading-snug break-words">
                    Active Mass Intention
                </p>

                <p class="text-2xl sm:text-3xl font-bold text-purple-700 mt-2">

                    @if(isset($activeMassIntentions))
                        {{ $activeMassIntentions }}
                    @elseif(isset($massIntentions))
                        {{ $massIntentions->where('status', 'Pending')->count() }}
                    @else
                        0
                    @endif

                </p>

            </div>

            <a
                href="{{ route('mass-intentions.index') }}"
                class="
                    text-purple-700
                    font-semibold
                    text-xs
                    sm:text-sm
                    hover:underline
                    mt-3
                    inline-block
                "
            >
                View Details
            </a>

        </div>


        <!-- DONATION THIS YEAR -->
        <div
            class="
                bg-green-100
                border
                border-green-300
                rounded-2xl
                p-4
                sm:p-5
                min-h-[130px]
                sm:min-h-[135px]
                flex
                flex-col
                justify-between
                min-w-0
            "
        >

            <div class="min-w-0">

                <p class="text-green-600 font-semibold text-sm sm:text-base leading-snug break-words">
                    Donation This Year
                </p>

                <p class="text-xl sm:text-3xl font-bold text-green-600 mt-2 break-words">

                    ₱{{ number_format(
                        $donationThisYear
                        ?? $totalDonations
                        ?? 0,
                        2
                    ) }}

                </p>

            </div>

            <a
                href="{{ route('donations.index') }}"
                class="
                    text-green-700
                    font-semibold
                    text-xs
                    sm:text-sm
                    hover:underline
                    mt-3
                    inline-block
                "
            >
                View History
            </a>

        </div>


        <!-- MY REQUEST -->
        <div
            class="
                bg-blue-100
                border
                border-blue-300
                rounded-2xl
                p-4
                sm:p-5
                min-h-[130px]
                sm:min-h-[135px]
                flex
                flex-col
                justify-between
                min-w-0
            "
        >

            <div class="min-w-0">

                <p class="text-blue-600 font-semibold text-sm sm:text-base leading-snug break-words">
                    My Request
                </p>

                <p class="text-2xl sm:text-3xl font-bold text-blue-600 mt-2">

                    @if(isset($myRequests))
                        {{ $myRequests }}
                    @elseif(isset($requestCount))
                        {{ $requestCount }}
                    @else
                        0
                    @endif

                </p>

            </div>

            <a
                href="{{ route('users.my-request') }}"
                class="
                    text-blue-700
                    font-semibold
                    text-xs
                    sm:text-sm
                    hover:underline
                    mt-3
                    inline-block
                "
            >
                View Status
            </a>

        </div>


        <!-- UPCOMING MASSES -->
        <div
            class="
                bg-orange-100
                border
                border-orange-300
                rounded-2xl
                p-4
                sm:p-5
                min-h-[130px]
                sm:min-h-[135px]
                flex
                flex-col
                justify-between
                min-w-0
            "
        >

            <div class="min-w-0">

                <p class="text-orange-600 font-semibold text-sm sm:text-base leading-snug break-words">
                    Upcoming Masses
                </p>

                <p class="text-2xl sm:text-3xl font-bold text-orange-600 mt-2">

                    {{ $upcomingSchedules->count() }}

                </p>

            </div>

            <a
                href="{{ route('mass-intentions.index') }}"
                class="
                    text-orange-700
                    font-semibold
                    text-xs
                    sm:text-sm
                    hover:underline
                    mt-3
                    inline-block
                "
            >
                View Schedule
            </a>

        </div>

    </section>


    <!-- MAIN DASHBOARD CONTENT -->
    <section
        class="grid grid-cols-1 gap-5 sm:gap-7 lg:grid-cols-2 mt-6 sm:mt-7"
        aria-label="Parish updates"
    >

        <!-- UPCOMING MASS SCHEDULE -->
        <article
            class="
                bg-white
                border
                border-gray-200
                rounded-2xl
                overflow-hidden
                min-w-0
            "
        >

            <!-- HEADER -->
            <div
                class="
                    px-4
                    py-4
                    sm:px-6
                    sm:py-5
                    border-b
                    border-gray-200
                    flex
                    items-center
                    justify-between
                    gap-3
                "
            >

                <h3 class="text-base sm:text-xl font-bold text-gray-900 leading-snug">
                    Upcoming Mass Schedule
                </h3>

                <a
                    href="{{ route('mass-intentions.index') }}"
                    class="
                        text-blue-600
                        font-semibold
                        text-xs
                        sm:text-sm
                        hover:underline
                        flex-shrink-0
                    "
                >
                    View All
                </a>

            </div>


            <!-- SCHEDULE LIST -->
            <div>

                @forelse ($upcomingSchedules->take(3) as $schedule)

                    <div
                        class="
                            px-4
                            py-4
                            sm:px-6
                            sm:py-5
                            border-b
                            border-gray-100
                            last:border-b-0
                        "
                    >

                        <div class="flex items-start gap-3 sm:gap-4">

                            <!-- CALENDAR ICON -->
                            <div
                                class="
                                    w-11
                                    h-11
                                    sm:w-14
                                    sm:h-14
                                    bg-blue-500
                                    rounded-xl
                                    flex
                                    items-center
                                    justify-center
                                    flex-shrink-0
                                "
                            >

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5 sm:w-7 sm:h-7 text-white"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M7 15h.01M12 15h.01M17 15h.01M7 18h.01M12 18h.01M17 18h.01"
                                    />
                                </svg>

                            </div>


                            <!-- SCHEDULE INFORMATION -->
                            <div class="flex-1 min-w-0">

                                <h4
                                    class="
                                        text-base
                                        sm:text-lg
                                        font-bold
                                        text-gray-900
                                        leading-snug
                                        break-words
                                    "
                                >

                                    {{ $schedule->mass_date->format('F d, Y') }}

                                    @if($schedule->mass_date->format('l'))
                                        ({{ $schedule->mass_date->format('l') }})
                                    @endif

                                </h4>


                                <p
                                    class="
                                        text-sm
                                        sm:text-base
                                        text-gray-700
                                        mt-1
                                        font-medium
                                        break-words
                                    "
                                >

                                    {{ $schedule->mass_date->format('h:i A') }}

                                    -

                                    {{ $schedule->title }}

                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    Intention requests close:
                                    {{ $schedule->closing_at?->format('M j, Y · g:i A') ?? 'Not set' }}
                                </p>

                            </div>

                        </div>

                    </div>

                @empty

                    <div
                        class="
                            py-12
                            sm:py-14
                            px-4
                            sm:px-6
                            text-center
                            text-gray-500
                        "
                    >

                        <div
                            class="
                                w-12
                                h-12
                                sm:w-14
                                sm:h-14
                                mx-auto
                                mb-4
                                bg-blue-100
                                rounded-xl
                                flex
                                items-center
                                justify-center
                            "
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 sm:w-7 sm:h-7 text-blue-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8 7V3m8 4V3m-9 4h10M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"
                                />
                            </svg>

                        </div>

                        <p class="font-semibold text-sm sm:text-base text-gray-700">
                            No upcoming Mass schedule available.
                        </p>

                        <p class="text-xs sm:text-sm mt-2">
                            Please check again later.
                        </p>

                    </div>

                @endforelse

            </div>

        </article>


        <!-- LATEST ANNOUNCEMENTS -->
        <article
            class="
                bg-white
                border
                border-gray-200
                rounded-2xl
                overflow-hidden
                min-w-0
            "
        >

            <!-- HEADER -->
            <div
                class="
                    px-4
                    py-4
                    sm:px-6
                    sm:py-5
                    border-b
                    border-gray-200
                    flex
                    items-center
                    justify-between
                    gap-3
                "
            >

                <h3 class="text-base sm:text-xl font-bold text-gray-900 leading-snug">
                    Latest Announcements
                </h3>

                <a
                    href="{{ route('users.announcements') }}"
                    class="
                        text-blue-600
                        font-semibold
                        text-xs
                        sm:text-sm
                        hover:underline
                        flex-shrink-0
                    "
                >
                    View All
                </a>

            </div>


            <!-- ANNOUNCEMENT LIST -->
            <div>

                @forelse ($latestAnnouncements->take(3) as $announcement)

                    <div
                        class="
                            px-4
                            py-4
                            sm:px-6
                            sm:py-5
                            border-b
                            border-gray-100
                            last:border-b-0
                        "
                    >

                        <div class="flex items-start gap-3 sm:gap-4">

                            <!-- BLUE DOT -->
                            <div class="pt-1.5 sm:pt-2 flex-shrink-0">

                                <span
                                    class="
                                        block
                                        w-2.5
                                        h-2.5
                                        sm:w-3
                                        sm:h-3
                                        rounded-full
                                        bg-blue-500
                                    "
                                ></span>

                            </div>


                            <!-- ANNOUNCEMENT -->
                            <div class="flex-1 min-w-0">

                                <h4
                                    class="
                                        text-base
                                        sm:text-lg
                                        font-bold
                                        text-gray-900
                                        leading-snug
                                        break-words
                                    "
                                >

                                    {{ $announcement->title }}

                                </h4>


                                @if($announcement->announcement_date)

                                    <p
                                        class="
                                            text-sm
                                            sm:text-base
                                            text-gray-700
                                            font-medium
                                            mt-2
                                        "
                                    >

                                        {{ $announcement->announcement_date->format('F d, Y') }}

                                    </p>

                                @elseif($announcement->created_at)

                                    <p
                                        class="
                                            text-sm
                                            sm:text-base
                                            text-gray-700
                                            font-medium
                                            mt-2
                                        "
                                    >

                                        {{ $announcement->created_at->format('F d, Y') }}

                                    </p>

                                @endif


                                <p
                                    class="
                                        text-sm
                                        text-gray-600
                                        mt-2
                                        leading-relaxed
                                        break-words
                                    "
                                >

                                    {{ \Illuminate\Support\Str::limit(
                                        $announcement->content,
                                        150
                                    ) }}

                                </p>

                            </div>

                        </div>

                    </div>

                @empty

                    <div
                        class="
                            py-12
                            sm:py-14
                            px-4
                            sm:px-6
                            text-center
                            text-gray-500
                        "
                    >

                        <div
                            class="
                                w-12
                                h-12
                                sm:w-14
                                sm:h-14
                                mx-auto
                                mb-4
                                bg-blue-100
                                rounded-xl
                                flex
                                items-center
                                justify-center
                            "
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 sm:w-7 sm:h-7 text-blue-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M7 8h10M7 12h10M7 16h6"
                                />
                            </svg>

                        </div>

                        <p class="font-semibold text-sm sm:text-base text-gray-700">
                            No announcements available yet.
                        </p>

                        <p class="text-xs sm:text-sm mt-2">
                            Please check again later.
                        </p>

                    </div>

                @endforelse

            </div>

        </article>

    </section>

</div>

@endsection