{{-- Payment state for a donation or Mass intention, with a "Pay with GCash" link when a GCash payment is due. --}}
@php
    /** @var \App\Payments\Payable $payable */
    $amountDue = $payable->paymentAmount();
@endphp

@if ($payable->isPaid())
    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">
        Paid{{ $payable->isOnline() ? ' · GCash' : ' · at parish' }}
    </span>
@elseif ($payable->status === 'Rejected' || $amountDue <= 0)
    <span class="text-xs text-slate-500">No payment due</span>
@elseif ($payable->isOnline() && $payable->isPaymentSubmitted())
    <div class="flex flex-col items-start gap-2">
        <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800">GCash receipt sent · awaiting parish confirmation</span>
        <a href="{{ $payRoute }}" class="text-xs font-semibold text-blue-700 underline hover:text-blue-900">View or re-upload receipt</a>
    </div>
@elseif ($payable->isOnline())
    <div class="flex flex-col items-start gap-2">
        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Awaiting GCash payment</span>
        <a href="{{ $payRoute }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#007dfe] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#0064cc] focus:outline-none focus:ring-4 focus:ring-blue-200">
            Pay ₱{{ number_format($amountDue, 2) }} with GCash
        </a>
    </div>
@else
    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Pay at parish office</span>
@endif
