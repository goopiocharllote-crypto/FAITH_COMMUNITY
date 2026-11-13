@extends('layouts.user')

@section('title', 'Booking Confirmation | Faith Community Platform')

@section('page-title', 'Booking Confirmation')

@section('content')

@php
    /** @var \App\Models\MassIntention $massIntention */
    $reference = $massIntention->referenceNumber();

    [$heading, $summary, $tone] = match ($massIntention->status) {
        'Approved' => [
            'Booking confirmed',
            'The parish office approved your Mass Intention. It will be offered at the Mass below.',
            'border-emerald-200 bg-emerald-50 text-emerald-900',
        ],
        'Rejected' => [
            'Booking not accepted',
            'The parish office could not accept this Mass Intention. Please contact the parish office or book another Mass.',
            'border-red-200 bg-red-50 text-red-900',
        ],
        default => [
            'Booking received',
            'Your Mass Intention was sent to the parish office for review. We will notify you by email and SMS once it is reviewed.',
            'border-blue-200 bg-blue-50 text-blue-900',
        ],
    };
@endphp

<div class="mx-auto max-w-2xl">

    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm print:border-0 print:shadow-none">

        <header class="border-b border-slate-200 px-6 py-5 text-center">
            <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Faith Community · Loboc Parish</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-900">Mass Intention Booking Confirmation</h2>
        </header>

        <div class="px-6 py-6">

            <div class="rounded-xl border px-5 py-4 {{ $tone }}">
                <p class="text-lg font-bold">{{ $heading }}</p>
                <p class="mt-1 text-sm">{{ $summary }}</p>
            </div>

            <div class="mt-6 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-5 text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Reference Number</p>
                <p id="booking-reference" class="mt-1 font-mono text-3xl font-bold tracking-wider text-slate-900">{{ $reference }}</p>
                <p class="mt-2 text-sm text-slate-600">Keep this number. Mention it when you visit or contact the parish office.</p>
                <button
                    type="button"
                    data-copy-reference="{{ $reference }}"
                    class="mt-3 inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-slate-200 print:hidden"
                >
                    Copy reference number
                </button>
            </div>

            <dl class="mt-6 divide-y divide-slate-100 text-sm">
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">Booked by</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $massIntention->user->name }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">Intention for</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $massIntention->intention_for }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">Intention type</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $massIntention->intention_type }}</dd>
                </div>
                @if ($massIntention->additional_message)
                    <div class="py-3">
                        <dt class="text-slate-500">Message</dt>
                        <dd class="mt-1 whitespace-pre-line text-slate-900">{{ $massIntention->additional_message }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">Mass</dt>
                    <dd class="text-right font-medium text-slate-900">
                        @if ($massIntention->schedule)
                            <span class="block">{{ $massIntention->schedule->title }}</span>
                        @endif
                        <span class="block">{{ $massIntention->mass_date->format('l, F j, Y') }}</span>
                        <span class="block">{{ $massIntention->mass_date->format('g:i A') }}</span>
                    </dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">Mobile number</dt>
                    <dd class="text-right font-medium text-slate-900">+63 {{ $massIntention->mobile }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">Offering</dt>
                    <dd class="text-right font-medium text-slate-900">₱{{ number_format((float) $massIntention->offering_amount, 2) }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-3">
                    <dt class="text-slate-500">Payment</dt>
                    <dd class="flex justify-end">
                        @include('user-components::payment-status', [
                            'payable' => $massIntention,
                            'payRoute' => route('payments.mass-intentions.pay', $massIntention),
                        ])
                    </dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-3">
                    <dt class="text-slate-500">Request status</dt>
                    <dd class="flex justify-end">
                        @include('user-components::request-status', [
                            'status' => $massIntention->status,
                            'churchReadAt' => $massIntention->church_read_at,
                        ])
                    </dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">Booked on</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $massIntention->created_at->format('M j, Y g:i A') }}</dd>
                </div>
            </dl>

        </div>

        <footer class="flex justify-end border-t border-slate-200 bg-slate-50 px-6 py-4">
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center justify-center rounded-lg bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200"
            >
                Back to Dashboard
            </a>
        </footer>

    </article>

</div>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-copy-reference]').forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copyReference);
                button.textContent = 'Copied!';
            } catch (error) {
                button.textContent = 'Select the number above to copy';
            }
        });
    });
</script>
@endpush
