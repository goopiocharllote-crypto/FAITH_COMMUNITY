@extends('layouts.user')

@section('title', 'Live Mass | Faith Community Platform')
@section('page-title', 'Live Mass')

@section('content')
    <header class="mb-7">
        <h2 class="text-3xl text-gray-900">
            Live Mass
        </h2>
        <p class="mt-1 text-gray-600">
            Join the parish Mass online. Livestream links appear here and on each upcoming Mass below.
        </p>
    </header>

    @if ($facebookLive)
        <section
            @class([
                'mb-8 flex flex-col gap-5 rounded-2xl border p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between',
                'border-gray-200 bg-white',
            ])
            aria-labelledby="facebook-live-heading"
            data-facebook-live
        >
            <div class="flex min-w-0 items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#1877F2] text-white" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="h-7 w-7" fill="currentColor"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.7 4.53-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.88v2.27h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z"/></svg>
                </span>
                <div class="min-w-0">

                    <h3 id="facebook-live-heading" class="text-xl font-bold text-gray-900">{{ $facebookLive->displayTitle() }}</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        @if ($facebookLive->embedUrl())
                            {{ $facebookLive->is_live ? 'The parish is streaming Mass right now. Watch it below, or open it in the Facebook app.' : 'Watch the parish Mass video below, or open it in the Facebook app.' }}
                        @else
                            {{ $facebookLive->is_live ? 'The parish is streaming Mass on Facebook right now.' : 'Watch the parish Mass video on Facebook.' }}
                            Opens in the Facebook app if it's installed.
                        @endif
                    </p>
                </div>
            </div>

            <a
                href="{{ $facebookLive->url }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#1877F2] px-6 py-3 font-semibold text-white shadow-lg shadow-blue-900/20 transition hover:bg-[#0f63d4] focus:outline-none focus:ring-4 focus:ring-blue-200"
            >
                Watch on Facebook
                <svg viewBox="0 0 20 20" class="h-5 w-5" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 0 0 1.06 0l7.22-7.22v5.69a.75.75 0 0 0 1.5 0v-7.5a.75.75 0 0 0-.75-.75h-7.5a.75.75 0 0 0 0 1.5h5.69l-7.22 7.22a.75.75 0 0 0 0 1.06Z" clip-rule="evenodd"/></svg>
            </a>
        </section>
    @endif

    @php($facebookEmbedUrl = $facebookLive?->embedUrl())

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-black" aria-label="Live Mass stream">
        @if ($facebookEmbedUrl)
            <div class="flex flex-wrap items-center justify-between gap-3 bg-gray-900 px-5 py-3 text-white">
                <div class="flex items-center gap-3">

                    <p class="font-semibold">{{ $facebookLive->displayTitle() }}</p>
                </div>
                <a href="{{ $facebookLive->url }}" target="_blank" rel="noopener noreferrer" class="rounded-lg bg-white px-3 py-1.5 text-sm font-semibold text-gray-900 hover:bg-gray-100">
                    Open in Facebook
                </a>
            </div>
            <div class="aspect-video w-full bg-black" data-facebook-player>
                <iframe
                    src="{{ $facebookEmbedUrl }}"
                    title="{{ $facebookLive->displayTitle() }} on Facebook"
                    class="h-full w-full border-0"
                    scrolling="no"
                    allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share; fullscreen"
                    allowfullscreen
                ></iframe>
            </div>
            <p class="bg-gray-900 px-5 py-2.5 text-xs text-gray-400">
                Video not playing? It may be private on Facebook or blocked by your browser. Use "Open in Facebook" instead.
            </p>
        @elseif ($liveSchedule)
            @php($embedUrl = $liveSchedule->livestreamEmbedUrl())
            <div class="flex flex-wrap items-center justify-between gap-3 bg-gray-900 px-5 py-3 text-white">
                <div class="flex items-center gap-3">
                    @unless ($liveSchedule->mass_date->isPast())
                        <span class="rounded-full bg-amber-500 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide text-gray-900">Starting soon</span>
                    @endunless
                    <div>
                        <p class="font-semibold">{{ $liveSchedule->title }}</p>
                        <p class="text-xs text-gray-300">{{ $liveSchedule->mass_date->format('l, F j · g:i A') }}</p>
                    </div>
                </div>
                <a href="{{ $liveSchedule->livestream_url }}" target="_blank" rel="noopener noreferrer" class="rounded-lg bg-white px-3 py-1.5 text-sm font-semibold text-gray-900 hover:bg-gray-100">
                    Open stream in new tab
                </a>
            </div>

            @if ($embedUrl)
                <div class="aspect-video w-full">
                    <iframe
                        src="{{ $embedUrl }}"
                        title="{{ $liveSchedule->title }} livestream"
                        class="h-full w-full"
                        allow="autoplay; encrypted-media; picture-in-picture; fullscreen"
                        allowfullscreen
                    ></iframe>
                </div>
            @else
                <div class="flex min-h-[300px] items-center justify-center px-6 text-center text-white">
                    <div>
                        <h3 class="text-2xl font-bold">The Mass is being streamed now</h3>
                        <p class="mt-2 text-gray-300">This stream opens on its own site.</p>
                        <a href="{{ $liveSchedule->livestream_url }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex rounded-xl bg-red-600 px-6 py-3 font-semibold text-white hover:bg-red-700">
                            Watch the livestream
                        </a>
                    </div>
                </div>
            @endif
        @else
            <div class="flex min-h-[450px] items-center justify-center px-6 text-center text-white">
                <div>
                    <div class="mb-5 text-6xl" aria-hidden="true">▣</div>
                    @if ($facebookLive?->is_live)
                        <h3 class="text-2xl font-bold">
                            We're live on Facebook
                        </h3>
                        <p class="mt-2 text-gray-300">
                            Tap "Watch on Facebook" above to join the Mass.
                        </p>
                    @else
                        <h3 class="text-2xl font-bold">
                            No Live Mass Right Now
                        </h3>
                        <p class="mt-2 text-gray-300">
                            The stream appears here 15 minutes before a Mass that has a livestream link.
                        </p>
                    @endif
                </div>
            </div>
        @endif
    </section>

    <section class="mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-xl font-bold text-gray-900">Upcoming Mass Schedules</h3>
            <p class="mt-1 text-sm text-gray-600">Parish schedules set by the administrator.</p>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse ($upcomingSchedules as $schedule)
                <article class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h4 class="font-semibold text-gray-900">{{ $schedule->title }}</h4>
                        <p class="mt-1 text-sm text-gray-600">{{ $schedule->mass_date->format('l, F j, Y · g:i A') }}</p>
                        <p class="mt-1 text-sm text-gray-500">Intention requests close: {{ $schedule->closing_at?->format('l, F j, Y · g:i A') ?? 'Not set' }}</p>
                    </div>
                    @if ($schedule->livestream_url)
                        <a href="{{ $schedule->livestream_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                            <span class="h-2 w-2 rounded-full bg-red-600" aria-hidden="true"></span>
                            Livestream link
                        </a>
                    @else
                        <span class="shrink-0 text-sm text-gray-400">Livestream link not posted yet</span>
                    @endif
                </article>
            @empty
                <p class="px-5 py-10 text-center text-sm text-gray-500">There are no upcoming Mass schedules right now.</p>
            @endforelse
        </div>
    </section>
@endsection
