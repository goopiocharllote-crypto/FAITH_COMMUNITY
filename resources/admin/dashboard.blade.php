@extends('layouts.admin')

@section('title', 'Admin Dashboard | Faith Community Platform')

@section('header-content')

    <h1 class="text-4xl text-gray-900">
        Welcome back, {{ auth()->user()->name }}.
    </h1>

@endsection


@section('content')


<!-- ===================================================== -->
<!-- STATISTICS -->
<!-- ===================================================== -->

<div
    class="
        grid
        grid-cols-1
        md:grid-cols-2
        xl:grid-cols-4
        gap-5
        mb-8
    "
>


    <!-- TOTAL MASS INTENTION -->

    <div
        class="
            bg-[#fffafa]
            border
            border-[#dfd0d0]
            rounded-2xl
            p-5
            min-h-[145px]
            relative
        "
    >

        <p class="font-bold text-gray-900 text-lg">
            Total Mass Intention
        </p>

        <p class="text-4xl font-bold text-black mt-3">
            {{ $totalMassIntentions }}
        </p>

        <p class="text-sm text-gray-800 mt-5">
            This Month
        </p>

        <div
            class="
                absolute
                right-5
                bottom-3
                text-5xl
                text-orange-500
            "
        >
            +
        </div>

    </div>


    <!-- PENDING REQUESTS -->

    <div
        class="
            bg-[#fffafa]
            border
            border-[#dfd0d0]
            rounded-2xl
            p-5
            min-h-[145px]
            relative
        "
    >

        <p class="font-bold text-gray-900 text-lg">
            Pending Requests
        </p>

        <p class="text-4xl font-bold text-black mt-3">
            {{ $pendingRequests }}
        </p>

        <p class="text-sm text-gray-800 mt-5">
            Needs Action
        </p>

        <div
            class="
                absolute
                right-5
                bottom-4
                text-4xl
            "
        >
            📋
        </div>

    </div>


    <!-- APPROVED REQUESTS -->

    <div
        class="
            bg-[#fffafa]
            border
            border-[#dfd0d0]
            rounded-2xl
            p-5
            min-h-[145px]
            relative
        "
    >

        <p class="font-bold text-gray-900 text-lg">
            Approved Requests
        </p>

        <p class="text-4xl font-bold text-black mt-3">
            {{ $approvedRequests }}
        </p>

        <p class="text-sm text-gray-800 mt-5">
            This Month
        </p>

        <div
            class="
                absolute
                right-5
                bottom-4
                text-4xl
            "
        >
            📋
        </div>

    </div>


    <!-- TOTAL DONATIONS -->

    <div
        class="
            bg-[#fffafa]
            border
            border-[#dfd0d0]
            rounded-2xl
            p-5
            min-h-[145px]
            relative
        "
    >

        <p class="font-bold text-gray-900 text-lg">
            Total Donations
        </p>

        <p class="text-3xl font-bold text-black mt-4">

            ₱{{ number_format($totalDonations, 2) }}

        </p>

        <p class="text-sm text-gray-800 mt-4">
            This Month
        </p>

        <div
            class="
                absolute
                right-4
                bottom-3
                text-4xl
            "
        >
            🙏
        </div>

    </div>

</div>



<!-- ===================================================== -->
<!-- TWO MAIN PANELS -->
<!-- ===================================================== -->

<div
    class="
        grid
        grid-cols-1
        xl:grid-cols-2
        gap-5
    "
>


    <!-- ================================================= -->
    <!-- UPCOMING MASS SCHEDULE -->
    <!-- ================================================= -->

    <section
        class="
            bg-[#fffafa]
            border
            border-[#dfd0d0]
            rounded-2xl
            overflow-hidden
        "
    >

        <div
            class="
                px-5
                py-4
                border-b
                border-[#dfd0d0]
                flex
                items-center
                justify-between
            "
        >

            <h2 class="text-xl font-bold text-gray-900">
                Upcoming Mass Schedule
            </h2>

            @if (Route::has('admin.schedules'))
                <a
                    href="{{ route('admin.schedules') }}"
                    class="
                    text-blue-600
                    font-semibold
                    hover:underline
                    "
                >
                    View Calendar
                </a>
            @endif

        </div>


        <div>

            @forelse ($upcomingMassSchedule as $schedule)

                <div
                    class="
                        px-5
                        py-4
                        border-b
                        border-[#dfd0d0]
                        flex
                        items-center
                        justify-between
                        gap-4
                    "
                >

                    <div class="flex items-start gap-3">

                        <div class="text-xl mt-1">
                            ▦
                        </div>

                        <div>

                            <p class="font-bold text-gray-900">

                                {{ $schedule->mass_date->format('F d, Y (l)') }}

                            </p>

                            <p class="text-sm text-gray-700 mt-1">

                                {{ $schedule->title }}

                                ·

                                {{ $schedule->mass_date->format('h:i A') }}

                            </p>

                        </div>

                    </div>


                </div>

            @empty

                <div
                    class="
                        py-14
                        text-center
                        text-gray-500
                    "
                >

                    <div class="text-4xl mb-3">
                        ▦
                    </div>

                    <p class="font-semibold">
                        No upcoming Mass Schedule
                    </p>

                    <p class="text-sm mt-1">
                        Create a schedule from Manage Schedules.
                    </p>

                </div>

            @endforelse

        </div>

    </section>



    <!-- ================================================= -->
    <!-- RECENT ACTIVITIES -->
    <!-- ================================================= -->

    <section
        class="
            bg-[#fffafa]
            border
            border-[#dfd0d0]
            rounded-2xl
            overflow-hidden
        "
    >

        <div
            class="
                px-5
                py-4
                border-b
                border-[#dfd0d0]
                flex
                items-center
                justify-between
            "
        >

            <h2 class="text-xl font-bold text-gray-900">
                Recent Activities
            </h2>

            @if (Route::has('admin.mass-intentions'))
                <a
                    href="{{ route('admin.mass-intentions') }}"
                    class="
                    text-blue-600
                    font-semibold
                    hover:underline
                    "
                >
                    View All
                </a>
            @endif

        </div>


        <div>

            @forelse ($recentActivities as $activity)

                <div
                    class="
                        px-5
                        py-4
                        border-b
                        border-[#dfd0d0]
                        flex
                        items-start
                        gap-4
                    "
                >

                    <div
                        class="
                            w-7
                            text-xl
                            flex-shrink-0
                        "
                    >

                        {{ $activity['icon'] }}

                    </div>


                    <div class="flex-1">

                        <p class="font-semibold text-gray-900">

                            {{ $activity['title'] }}

                        </p>

                        <p class="text-sm text-gray-600 mt-1">

                            {{ $activity['date']->format('M d, Y h:i A') }}

                        </p>

                    </div>

                </div>

            @empty

                <div
                    class="
                        py-14
                        text-center
                        text-gray-500
                    "
                >

                    <div class="text-4xl mb-3">
                        ◌
                    </div>

                    <p class="font-semibold">
                        No recent activities
                    </p>

                </div>

            @endforelse

        </div>

    </section>

</div>

@endsection