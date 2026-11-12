@extends('layouts.admin')

@section('title', 'Live Mass | Admin')

@section('page-title', 'Live Mass')
@section('page-description', 'Share the parish Facebook Live link with parishioners')

@section('content')
    @php
        $inputClass = fn (string $field) => 'w-full rounded-lg border px-4 py-3 text-gray-900 outline-none transition focus:ring-4 '
            .($errors->has($field) ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-gray-300 focus:border-blue-600 focus:ring-blue-100');
    @endphp

    <section class="mb-8 rounded-xl border border-gray-200 bg-white p-6" aria-labelledby="current-live-heading">
        <h3 id="current-live-heading" class="mb-4 text-xl font-bold">What parishioners see now</h3>

        @if ($facebookLive)
            <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-gray-50 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        @if ($facebookLive->is_live)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-600 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide text-white">
                                <span class="h-2 w-2 animate-pulse rounded-full bg-white" aria-hidden="true"></span> Live now
                            </span>
                        @else
                            <span class="rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide text-gray-700">Link posted · not live</span>
                        @endif
                        <span class="text-xs text-gray-500">Updated {{ $facebookLive->updated_at->format('M j, Y · g:i A') }}</span>
                    </div>
                    <p class="font-semibold text-gray-900">{{ $facebookLive->displayTitle() }}</p>
                    <a href="{{ $facebookLive->url }}" target="_blank" rel="noopener noreferrer" class="mt-1 block break-all text-sm font-medium text-blue-700 hover:underline">{{ $facebookLive->url }}</a>
                    @if ($facebookLive->embedUrl())
                        <p class="mt-2 text-sm font-medium text-emerald-700">✓ The video plays right on the parishioners' Live Mass page.</p>
                        <div class="mt-4 aspect-video w-full max-w-md overflow-hidden rounded-lg bg-black">
                            <iframe src="{{ $facebookLive->embedUrl() }}" title="Preview of {{ $facebookLive->displayTitle() }}" class="h-full w-full border-0" scrolling="no" allow="encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>
                        </div>
                    @else
                        <p class="mt-2 text-sm font-medium text-amber-700">This is a page link, so parishioners only get a "Watch on Facebook" button. To show the video on the page, paste the link of the live video itself.</p>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.live-mass.destroy') }}" class="shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full rounded-lg border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-100 sm:w-auto">
                        Remove link
                    </button>
                </form>
            </div>
        @else
            <p class="rounded-xl border border-dashed border-gray-300 px-5 py-8 text-center text-sm text-gray-500">
                No Facebook Live link is posted. Parishioners see "No Live Mass Right Now".
            </p>
        @endif
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6" aria-labelledby="live-form-heading">
        <h3 id="live-form-heading" class="mb-1 text-xl font-bold">{{ $facebookLive ? 'Update the Facebook Live link' : 'Post a Facebook Live link' }}</h3>
        <p class="mb-5 text-sm text-gray-600">
            Start your live video on Facebook, open the video, then copy its link (tap <strong>Share → Copy link</strong>) and paste it here.
            A link to the video itself plays right on the parishioners' page; a link to your Facebook page only opens Facebook.
            Posting a new link replaces the old one.
        </p>

        <form method="POST" action="{{ route('admin.live-mass.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="url" class="mb-2 block font-semibold text-gray-800">Facebook Live link</label>
                <input
                    id="url"
                    type="url"
                    name="url"
                    value="{{ old('url', $facebookLive?->url) }}"
                    required
                    maxlength="500"
                    inputmode="url"
                    autocapitalize="none"
                    spellcheck="false"
                    placeholder="https://www.facebook.com/yourparish/videos/1234567890"
                    aria-describedby="url-hint"
                    class="{{ $inputClass('url') }}"
                >
                <p id="url-hint" class="mt-1.5 text-xs text-gray-500">Accepted: facebook.com, m.facebook.com, fb.watch and fb.com links.</p>
                @error('url')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="title" class="mb-2 block font-semibold text-gray-800">
                    Title <span class="font-normal text-gray-500">(optional)</span>
                </label>
                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ old('title', $facebookLive?->title) }}"
                    maxlength="120"
                    placeholder="{{ \App\Models\FacebookLive::DEFAULT_TITLE }}"
                    class="{{ $inputClass('title') }}"
                >
                @error('title')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
                <input type="hidden" name="is_live" value="0">
                <input
                    type="checkbox"
                    name="is_live"
                    value="1"
                    @checked(old('is_live', $facebookLive ? $facebookLive->is_live : true))
                    class="mt-1 h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500"
                >
                <span>
                    <span class="block font-semibold text-gray-800">We're live now</span>
                    <span class="block text-sm text-gray-600">Shows a red LIVE badge to parishioners. Untick it after Mass to keep the link as a replay.</span>
                </span>
            </label>

            <button type="submit" class="rounded-lg bg-blue-700 px-6 py-3 font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200">
                {{ $facebookLive ? 'Update link' : 'Post link' }}
            </button>
        </form>
    </section>
@endsection
