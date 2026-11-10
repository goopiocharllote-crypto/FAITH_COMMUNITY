<section id="mass-intentions" class="scroll-mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div>
            <h3 class="text-xl font-bold text-slate-900">Mass Intentions</h3>
            <p class="mt-1 text-sm text-slate-600">Choose an admin-published Mass schedule to submit an intention request.</p>
        </div>
        <a href="{{ route('mass-intentions.index') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200">
            Submit a New Mass Intention
        </a>
    </div>

    <div class="border-b border-slate-200 p-5 sm:px-6">
        <h4 class="font-semibold text-slate-900">Available Mass Schedules</h4>
        <p class="mt-1 text-sm text-slate-600">Requests are accepted only until the closing date and time set by the parish.</p>

        <div class="mt-4 grid gap-3 md:grid-cols-2">
            @forelse ($upcomingSchedules as $schedule)
                @php
                    $requestOpen = $schedule->closing_at
                        && $schedule->closing_at->isFuture()
                        && $schedule->mass_date->isFuture()
                        && $schedule->closing_at->lt($schedule->mass_date);
                @endphp
                <article class="flex flex-col justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center">
                    <div>
                        <h5 class="font-semibold text-slate-900">{{ $schedule->title }}</h5>
                        <p class="mt-1 text-sm text-slate-600">{{ $schedule->mass_date->format('l, M j, Y · g:i A') }}</p>
                        <p class="mt-1 text-sm text-slate-500">Intention requests close: {{ $schedule->closing_at?->format('M j, Y · g:i A') ?? 'Not set' }}</p>
                    </div>
                    @if ($requestOpen)
                        <a href="{{ route('mass-intentions.index', ['schedule_id' => $schedule->id]) }}" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200">
                            Choose this Mass
                        </a>
                    @else
                        <span class="inline-flex shrink-0 items-center justify-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-600" aria-disabled="true">
                            Requests closed
                        </span>
                    @endif
                </article>
            @empty
                <p class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600 md:col-span-2">There are no upcoming Mass schedules available.</p>
            @endforelse
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th scope="col" class="px-5 py-3">For</th>
                    <th scope="col" class="px-5 py-3">Type</th>
                    <th scope="col" class="px-5 py-3">Mass date</th>
                    <th scope="col" class="px-5 py-3">Status</th>
                    <th scope="col" class="px-5 py-3">Offering</th>
                    <th scope="col" class="px-5 py-3">Payment</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($massIntentions as $massIntention)
                    <tr>
                        <td class="px-5 py-4 font-medium text-slate-900">{{ $massIntention->intention_for }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $massIntention->intention_type }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $massIntention->mass_date->format('M d, Y h:i A') }}</td>
                        <td class="px-5 py-4">@include('user-components::request-status', ['status' => $massIntention->status, 'churchReadAt' => $massIntention->church_read_at])</td>
                        <td class="px-5 py-4 text-slate-600">₱{{ number_format((float) $massIntention->offering_amount, 2) }}</td>
                        <td class="px-5 py-4">@include('user-components::payment-status', ['payable' => $massIntention, 'payRoute' => route('payments.mass-intentions.pay', $massIntention)])</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-500">You have not submitted any Mass Intentions yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
