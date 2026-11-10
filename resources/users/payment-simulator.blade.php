@extends('layouts.user')

@section('title', 'GCash Checkout (Test Mode) | Faith Community Platform')
@section('page-title', 'GCash Checkout')

@section('content')
    <div class="mx-auto max-w-md">
        <div role="note" class="mb-5 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-semibold">Test mode — no real money moves.</p>
            <p class="mt-1">This simulated checkout stands in for GCash until the parish sets <code class="rounded bg-amber-100 px-1">PAYMENT_GATEWAY=paymongo</code> with PayMongo keys.</p>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="checkout-title">
            <div class="bg-[#007dfe] px-6 py-5 text-white">
                <p class="text-sm font-medium text-white/80">Pay with GCash</p>
                <h2 id="checkout-title" class="mt-1 text-3xl font-bold">₱{{ number_format((float) $payment->amount, 2) }}</h2>
            </div>

            <dl class="divide-y divide-slate-100 px-6 text-sm">
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">Merchant</dt>
                    <dd class="text-right font-medium text-slate-900">Faith Community Loboc Parish</dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">For</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $payable->paymentDescription() }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">Reference</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $payable->paymentReference() }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-slate-500">GCash account</dt>
                    <dd class="text-right font-medium text-slate-900">{{ auth()->user()->mobile ? '0'.substr(auth()->user()->mobile, 0, 3).' ••• '.substr(auth()->user()->mobile, -4) : '—' }}</dd>
                </div>
            </dl>

            <div class="space-y-3 border-t border-slate-100 p-6">
                @if ($payment->isPending())
                    <form method="POST" action="{{ route('payments.simulator.complete', $payment) }}">
                        @csrf
                        <input type="hidden" name="decision" value="pay">
                        <button type="submit" class="w-full rounded-xl bg-[#007dfe] py-3.5 font-semibold text-white transition hover:bg-[#0064cc] focus:outline-none focus:ring-4 focus:ring-blue-200">
                            Pay ₱{{ number_format((float) $payment->amount, 2) }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('payments.simulator.complete', $payment) }}">
                        @csrf
                        <input type="hidden" name="decision" value="cancel">
                        <button type="submit" class="w-full rounded-xl border border-slate-300 py-3 font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-200">
                            Cancel and return to parish site
                        </button>
                    </form>
                @else
                    <p class="text-center text-sm text-slate-600">This checkout is already {{ $payment->status }}.</p>
                    <a href="{{ $payable->paymentReturnRoute() }}" class="block w-full rounded-xl border border-slate-300 py-3 text-center font-semibold text-slate-700 hover:bg-slate-50">Back to parish site</a>
                @endif
            </div>
        </section>
    </div>
@endsection
