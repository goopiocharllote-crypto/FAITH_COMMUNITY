{{-- Payment state for a donation or Mass intention, with a "Pay with GCash" button when an online payment is due. --}}
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
@elseif ($payable->isOnline())
    <div class="flex flex-col items-start gap-2">
        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Awaiting GCash payment</span>
        <form method="POST" action="{{ $payRoute }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-[#007dfe] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#0064cc] focus:outline-none focus:ring-4 focus:ring-blue-200">
                Pay ₱{{ number_format($amountDue, 2) }} with GCash
            </button>
        </form>
    </div>
@else
    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Pay at parish office</span>
@endif
