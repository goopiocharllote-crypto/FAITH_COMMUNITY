@extends('layouts.admin')

@section('title', 'Mass Intentions | Admin')
@section('page-title', 'Mass Intentions')
@section('page-description', 'Review parishioner requests and track Mass intentions read in church.')

@section('content')
    @if (session('mass_success'))
        <div role="status" aria-live="polite" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-semibold text-emerald-800">
            {{ session('mass_success') }}
        </div>
    @endif

    @if ($errors->has('mass_intention'))
        <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 font-semibold text-red-800">
            {{ $errors->first('mass_intention') }}
        </div>
    @endif

    @php
        $totalRequests = $statusCounts->sum();
    @endphp

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.mass-intentions') }}" class="rounded-xl border bg-white p-5 shadow-sm transition hover:border-blue-300">
            <p class="text-sm font-medium text-slate-500">All requests</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalRequests }}</p>
        </a>
        <a href="{{ route('admin.mass-intentions', ['status' => 'Pending']) }}" class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm transition hover:border-amber-400">
            <p class="text-sm font-medium text-amber-800">Pending review</p>
            <p class="mt-2 text-3xl font-bold text-amber-900">{{ $statusCounts->get('Pending', 0) }}</p>
        </a>
        <a href="{{ route('admin.mass-intentions', ['status' => 'Approved']) }}" class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm transition hover:border-emerald-400">
            <p class="text-sm font-medium text-emerald-800">Approved</p>
            <p class="mt-2 text-3xl font-bold text-emerald-900">{{ $statusCounts->get('Approved', 0) }}</p>
        </a>
        <a href="{{ route('admin.mass-intentions', ['status' => 'Rejected']) }}" class="rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm transition hover:border-rose-400">
            <p class="text-sm font-medium text-rose-800">Rejected</p>
            <p class="mt-2 text-3xl font-bold text-rose-900">{{ $statusCounts->get('Rejected', 0) }}</p>
        </a>
    </div>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">
                    {{ $activeStatus ? $activeStatus.' Mass Intentions' : 'All Mass Intentions' }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">New and pending requests appear first.</p>
            </div>
            <nav aria-label="Filter Mass Intentions" class="flex flex-wrap gap-2 text-sm">
                <a href="{{ route('admin.mass-intentions') }}" @class([
                    'rounded-full px-3 py-1.5 font-semibold',
                    'bg-slate-900 text-white' => ! $activeStatus,
                    'bg-slate-100 text-slate-700 hover:bg-slate-200' => $activeStatus,
                ])>All</a>
                @foreach (['Pending', 'Approved', 'Rejected'] as $status)
                    <a href="{{ route('admin.mass-intentions', ['status' => $status]) }}" @class([
                        'rounded-full px-3 py-1.5 font-semibold',
                        'bg-slate-900 text-white' => $activeStatus === $status,
                        'bg-slate-100 text-slate-700 hover:bg-slate-200' => $activeStatus !== $status,
                    ])>
                        {{ $status }}
                        <span class="ml-1 opacity-75">{{ $statusCounts->get($status, 0) }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="border-b border-rose-100 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th scope="col" class="px-5 py-4">Reference No.</th>
                        <th scope="col" class="px-5 py-4">Request / Parishioner</th>
                        <th scope="col" class="px-5 py-4">Mass date &amp; time</th>
                        <th scope="col" class="px-5 py-4">Status</th>
                        <th scope="col" class="px-5 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rose-100">
                    @forelse ($massIntentions as $massIntention)
                        <tr class="align-top transition hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-5 py-4 font-medium text-slate-700">
                                MI-{{ $massIntention->created_at->format('Y') }}-{{ str_pad((string) $massIntention->id, 6, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-900">{{ $massIntention->intention_for }}</p>
                                <p class="mt-1 text-slate-700">{{ $massIntention->user->name ?? 'Unknown parishioner' }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $massIntention->intention_type }}
                                    @if ($massIntention->user?->email)
                                        · {{ $massIntention->user->email }}
                                    @endif
                                </p>
                                <details class="mt-2">
                                    <summary class="cursor-pointer text-xs font-semibold text-sky-700 hover:text-sky-900">View request details</summary>
                                    <div class="mt-2 space-y-1 rounded-lg bg-slate-50 p-3 text-xs text-slate-600">
                                        <p><span class="font-semibold">Mobile:</span> {{ $massIntention->mobile }}</p>
                                        <p><span class="font-semibold">Offering:</span> ₱{{ number_format((float) $massIntention->offering_amount, 2) }}</p>
                                        <p><span class="font-semibold">Payment:</span> {{ $massIntention->isOnline() ? 'GCash (QR code)' : 'At parish office' }} · {{ $massIntention->isPaid() ? 'Paid' : ($massIntention->isPaymentSubmitted() ? 'Receipt submitted' : 'Not yet paid') }}</p>
                                        @php($gcashPayment = $massIntention->isOnline() ? app(\App\Payments\GcashPayments::class)->latestPayment($massIntention) : null)
                                        @if ($gcashPayment)
                                            @if ($gcashPayment->gateway_payment_id)
                                                <p><span class="font-semibold">GCash reference no.:</span> {{ $gcashPayment->gateway_payment_id }}</p>
                                            @endif
                                            <p><span class="font-semibold">Amount to match:</span> ₱{{ number_format((float) $massIntention->offering_amount, 2) }}</p>
                                            @if ($gcashPayment->receipt)
                                                <p><a href="{{ route('payments.receipt', $gcashPayment) }}" target="_blank" rel="noopener" class="font-semibold text-sky-700 underline hover:text-sky-900">View GCash receipt</a></p>
                                            @endif
                                        @endif
                                        @if ($massIntention->additional_message)
                                            <p class="whitespace-pre-line"><span class="font-semibold">Message:</span> {{ $massIntention->additional_message }}</p>
                                        @endif
                                        <p><span class="font-semibold">Submitted:</span> {{ $massIntention->created_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </details>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-700">
                                {{ $massIntention->mass_date->format('M d, Y') }}
                                <p class="mt-1 text-xs text-slate-500">{{ $massIntention->mass_date->format('h:i A') }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                @if ($massIntention->status === 'Approved')
                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Approved</span>
                                @elseif ($massIntention->status === 'Rejected')
                                    <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800">Rejected</span>
                                @else
                                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">Pending</span>
                                @endif
                                @if ($massIntention->church_read_at)
                                    <p class="mt-2 text-xs font-semibold text-emerald-700">Read in church</p>
                                    <p class="text-xs text-slate-500">{{ $massIntention->church_read_at->format('M d, Y h:i A') }}</p>
                                @endif
                                <p class="mt-2 text-xs">
                                    @if ($massIntention->isPaid())
                                        <span class="font-semibold text-emerald-700">Offering paid</span>
                                        <span class="text-slate-500">· {{ $massIntention->isOnline() ? 'GCash' : 'at parish' }}</span>
                                    @elseif ((float) $massIntention->offering_amount <= 0)
                                        <span class="text-slate-500">No offering</span>
                                    @elseif ($massIntention->isPaymentSubmitted())
                                        <span class="font-semibold text-sky-700">GCash receipt submitted</span>
                                        <span class="text-slate-500">· open the receipt, match it in the parish GCash, then confirm</span>
                                    @else
                                        <span class="font-semibold text-amber-700">Offering unpaid</span>
                                        <span class="text-slate-500">· {{ $massIntention->isOnline() ? 'awaiting GCash' : 'pay at parish' }}</span>
                                    @endif
                                </p>
                                @if (! $massIntention->isPaid() && (! $massIntention->isOnline() || $massIntention->isPaymentSubmitted()) && (float) $massIntention->offering_amount > 0 && $massIntention->status !== 'Rejected')
                                    <form method="POST" action="{{ route('admin.mass-intentions.offering-received', $massIntention) }}" class="mt-2">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-lg border border-emerald-600 px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 focus:outline-none focus:ring-4 focus:ring-emerald-100">{{ $massIntention->isOnline() ? 'Confirm GCash payment' : 'Mark offering received' }}</button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($massIntention->status === 'Pending')
                                    <div class="flex flex-wrap gap-2">
                                        <form method="POST" action="{{ route('admin.mass-intentions.approve', $massIntention) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.mass-intentions.reject', $massIntention) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700 focus:outline-none focus:ring-4 focus:ring-rose-200">Reject</button>
                                        </form>
                                    </div>
                                @elseif ($massIntention->status === 'Approved' && ! $massIntention->church_read_at)
                                    <form method="POST" action="{{ route('admin.mass-intentions.read-in-church', $massIntention) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-lg bg-sky-700 px-3 py-2 text-xs font-semibold text-white hover:bg-sky-800 focus:outline-none focus:ring-4 focus:ring-sky-200">Mark read in church</button>
                                    </form>
                                @elseif ($massIntention->status === 'Approved')
                                    <form method="POST" action="{{ route('admin.mass-intentions.unread-in-church', $massIntention) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Undo read status</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">No action available</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-14 text-center">
                                <p class="font-semibold text-slate-700">
                                    {{ $activeStatus ? 'No '.$activeStatus.' Mass Intentions found.' : 'No Mass Intention requests have been received yet.' }}
                                </p>
                                <p class="mt-1 text-sm text-slate-500">When a parishioner submits a request, it will appear here for review.</p>
                                @if ($activeStatus)
                                    <a href="{{ route('admin.mass-intentions') }}" class="mt-3 inline-block text-sm font-semibold text-sky-700 hover:underline">View all requests</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($massIntentions->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">
                {{ $massIntentions->links() }}
            </div>
        @endif
    </section>
@endsection
