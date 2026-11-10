<section id="prayer-requests" class="scroll-mt-6 space-y-5">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h3 class="text-xl font-bold text-slate-900">Submit a Prayer Request</h3>
        <p class="mt-1 text-sm text-slate-600">Share a prayer intention with the parish. Requests are only visible to you and authorized parish administrators.</p>

        <form method="POST" action="{{ route('users.prayer-requests.store') }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <label for="prayer-title" class="mb-1.5 block text-sm font-semibold text-slate-700">Title</label>
                <input id="prayer-title" name="title" type="text" maxlength="120" required value="{{ old('title') }}" aria-describedby="prayer-title-help @error('title') prayer-title-error @enderror" class="w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">
                <p id="prayer-title-help" class="mt-1 text-xs text-slate-500">Up to 120 characters.</p>
                @error('title')
                    <p id="prayer-title-error" class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="prayer-request" class="mb-1.5 block text-sm font-semibold text-slate-700">Prayer request</label>
                <textarea id="prayer-request" name="request" rows="5" maxlength="3000" required aria-describedby="prayer-request-help @error('request') prayer-request-error @enderror" class="w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">{{ old('request') }}</textarea>
                <p id="prayer-request-help" class="mt-1 text-xs text-slate-500">Please do not include sensitive personal information. Up to 3,000 characters.</p>
                @error('request')
                    <p id="prayer-request-error" class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="rounded-lg bg-blue-900 px-5 py-3 font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200">
                Submit a New Prayer Request
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h3 class="text-lg font-bold text-slate-900">Your Prayer Requests</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($prayerRequests as $prayerRequest)
                <article class="p-5 sm:px-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h4 class="font-semibold text-slate-900">{{ $prayerRequest->title }}</h4>
                            <p class="mt-1 text-xs text-slate-500">Submitted {{ $prayerRequest->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        @include('user-components::request-status', ['status' => $prayerRequest->status])
                    </div>
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $prayerRequest->request }}</p>
                </article>
            @empty
                <p class="px-5 py-8 text-center text-sm text-slate-500">You have not submitted any Prayer Requests yet.</p>
            @endforelse
        </div>
    </div>
</section>
