@extends('layouts.user')

@section('title', 'Announcements | Faith Community Platform')
@section('page-title', 'Announcements')

@section('content')
    @php
        $announcements = $announcements ?? collect();
    @endphp

    <header class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900">
            Parish Announcements
        </h2>
        <p class="mt-2 text-gray-600">
            Stay updated with the latest news, activities, and important
            announcements from the parish.
        </p>
    </header>

    <section class="mb-8 overflow-hidden rounded-2xl border border-blue-200 bg-white shadow-sm" aria-label="Upcoming Mass schedules">
        <div class="border-b border-blue-100 bg-blue-50 px-6 py-5">
            <h3 class="text-xl font-bold text-gray-900">Upcoming Mass Schedules</h3>
            <p class="mt-1 text-sm text-gray-600">View upcoming Mass times, livestream links, and request an intention for a schedule. <a href="{{ route('users.live-mass') }}" class="font-semibold text-blue-700 hover:underline">Go to Live Mass</a></p>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse ($upcomingSchedules as $schedule)
                <article class="flex flex-col gap-3 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h4 class="font-semibold text-gray-900">{{ $schedule->title }}</h4>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $schedule->mass_date->format('l, F j, Y · g:i A') }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            Intention requests close:
                            {{ $schedule->closing_at?->format('l, F j, Y · g:i A') ?? 'Not set' }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($schedule->livestream_url)
                            <a
                                href="{{ $schedule->livestream_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex w-fit items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                            >
                                <span class="h-2 w-2 rounded-full bg-red-600" aria-hidden="true"></span>
                                Watch livestream
                            </a>
                        @endif
                        @if ($schedule->closing_at?->isFuture() && $schedule->mass_date->isFuture())
                            <a
                                href="{{ route('mass-intentions.index', ['schedule_id' => $schedule->id]) }}"
                                class="inline-flex w-fit items-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-800"
                            >
                                Request a Mass Intention
                            </a>
                        @else
                            <span class="inline-flex w-fit items-center rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600">
                                Requests closed
                            </span>
                        @endif
                    </div>
                </article>
            @empty
                <p class="px-6 py-8 text-center text-sm text-gray-500">
                    There are no upcoming Mass schedules right now.
                </p>
            @endforelse
        </div>
    </section>

    <section class="space-y-5" aria-label="Parish announcements">
        @forelse ($announcements as $announcement)
            <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md">
                <div class="flex gap-5">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-xl text-blue-600" aria-hidden="true">
                        📢
                    </div>

                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900">
                            {{ $announcement->title }}
                        </h3>

                        <p class="mt-1 text-sm {{ $announcement->announcement_date ? 'text-gray-500' : 'text-gray-400' }}">
                            {{ $announcement->announcement_date?->format('F d, Y') ?? 'Date not specified' }}
                        </p>

                        <p class="mt-4 whitespace-pre-line leading-7 text-gray-700">
                            {{ $announcement->content }}
                        </p>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center">
                <div class="mb-5 text-6xl" aria-hidden="true">📢</div>

                <h3 class="text-xl font-bold text-gray-700">
                    No Announcements Yet
                </h3>

                <p class="mx-auto mt-2 max-w-md text-gray-500">
                    There are currently no active parish announcements. New
                    announcements from the parish will appear here.
                </p>
            </div>
        @endforelse
    </section>
@endsection
