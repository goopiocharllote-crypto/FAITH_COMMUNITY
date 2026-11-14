@extends('layouts.user')

@section('title', 'Pay with GCash | Faith Community Platform')
@section('page-title', 'Pay with GCash')

@section('content')

@php
    /** @var \App\Payments\Payable $payable */
    $accountName = config('payments.gcash.account_name');
    $accountNumber = config('payments.gcash.account_number');
@endphp

<div class="mx-auto max-w-lg">

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="gcash-title">

        <div class="bg-[#007dfe] px-6 py-5 text-white">
            <p class="text-sm font-medium text-white/80">Pay with GCash</p>
            <h2 id="gcash-title" class="mt-1 text-3xl font-bold">₱{{ number_format($payable->paymentAmount(), 2) }}</h2>
            <p class="mt-1 text-sm text-white/90">{{ $payable->paymentDescription() }} · {{ $payable->paymentReference() }}</p>
        </div>

        <div class="px-6 py-6">

            <ol class="space-y-2 text-sm text-slate-700">
                <li><span class="font-semibold text-slate-900">1.</span> Open your <strong>GCash</strong> app and tap <strong>Pay QR</strong>.</li>
                <li><span class="font-semibold text-slate-900">2.</span> Scan the parish QR code below. GCash does not fill in the amount, so <strong>type exactly ₱{{ number_format($payable->paymentAmount(), 2) }}</strong> in the Amount box.</li>
                <li><span class="font-semibold text-slate-900">3.</span> After paying, GCash shows a <strong>receipt</strong>. Take a screenshot of it.</li>
                <li><span class="font-semibold text-slate-900">4.</span> Upload the receipt below. The parish office will check it and confirm your payment.</li>
            </ol>

            <div class="mt-6 flex flex-col items-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-5 text-center">
                @if ($qrUrl)
                    <img src="{{ $qrUrl }}" alt="Parish GCash QR code" class="h-auto w-full max-w-xs rounded-lg bg-white p-2 shadow-sm">
                    @if ($accountName || $accountNumber)
                        <p class="mt-3 text-sm font-semibold text-slate-900">{{ $accountName }}</p>
                        @if ($accountNumber)
                            <p class="text-sm text-slate-600">{{ $accountNumber }}</p>
                        @endif
                    @endif
                @else
                    <p class="font-semibold text-slate-900">The GCash QR code is not available yet.</p>
                    <p class="mt-1 text-sm text-slate-600">Please contact the parish office, or choose to pay at the parish office instead.</p>
                @endif
            </div>

            <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-4 text-center">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-800">Type this amount in GCash</p>
                <p class="mt-1 text-3xl font-bold text-blue-900" data-amount-display>₱{{ number_format($payable->paymentAmount(), 2) }}</p>
                <p class="mt-1 text-xs text-blue-800">The parish office will check that the amount on your receipt matches.</p>
                <button
                    type="button"
                    data-copy-amount="{{ number_format($payable->paymentAmount(), 2, '.', '') }}"
                    class="mt-3 inline-flex items-center rounded-lg border border-blue-300 bg-white px-3 py-1.5 text-xs font-semibold text-blue-800 transition hover:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-200"
                >
                    Copy amount
                </button>
            </div>

            @if ($submittedPayment)
                <div role="status" class="mt-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                    <p class="font-semibold">Receipt sent. Waiting for the parish office to confirm.</p>
                    @if ($submittedPayment->receipt)
                        <p class="mt-1"><a href="{{ route('payments.receipt', $submittedPayment) }}" target="_blank" rel="noopener" class="font-semibold underline">View the receipt you sent</a></p>
                    @endif
                    <p class="mt-1">Sent the wrong picture? Upload the correct receipt below.</p>
                </div>
            @endif

            <form method="POST" action="{{ $submitRoute }}" enctype="multipart/form-data" class="mt-6 space-y-5" novalidate>
                @csrf

                <div>
                    <label for="receipt" class="mb-1.5 block text-sm font-semibold text-slate-700">GCash receipt (picture or screenshot)</label>
                    <input
                        id="receipt"
                        name="receipt"
                        type="file"
                        accept="image/png,image/jpeg,image/webp"
                        required
                        aria-describedby="receipt-hint @error('receipt') receipt-error @enderror"
                        class="block w-full rounded-lg border border-slate-300 bg-white text-sm text-slate-700 shadow-sm file:mr-4 file:border-0 file:bg-blue-900 file:px-4 file:py-3 file:font-semibold file:text-white hover:file:bg-blue-800 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100"
                    >
                    <p id="receipt-hint" class="mt-1.5 text-xs text-slate-500">JPG, PNG or WEBP, up to {{ rtrim(rtrim(number_format($maxReceiptMb, 1), '0'), '.') }} MB. The receipt must show the amount and the reference number.</p>
                    @error('receipt')
                        <p id="receipt-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                    <img id="receipt-preview" alt="Preview of the receipt you chose" class="mt-3 hidden max-h-64 w-auto rounded-lg border border-slate-200">
                </div>

                <div>
                    <label for="reference_number" class="mb-1.5 block text-sm font-semibold text-slate-700">GCash reference number <span class="font-normal text-slate-500">(optional)</span></label>
                    <input
                        id="reference_number"
                        name="reference_number"
                        type="text"
                        inputmode="numeric"
                        autocomplete="off"
                        maxlength="30"
                        value="{{ old('reference_number') }}"
                        placeholder="e.g. 1234 567 890 123"
                        aria-describedby="reference-hint @error('reference_number') reference-error @enderror"
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100"
                    >
                    <p id="reference-hint" class="mt-1.5 text-xs text-slate-500">Shown on your receipt as "Ref No." It has 13 digits.</p>
                    @error('reference_number')
                        <p id="reference-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-xl bg-[#007dfe] py-3.5 font-semibold text-white transition hover:bg-[#0064cc] focus:outline-none focus:ring-4 focus:ring-blue-200">
                    Send receipt
                </button>
            </form>

            <a href="{{ $payable->paymentReturnRoute() }}" class="mt-4 block w-full rounded-xl border border-slate-300 py-3 text-center font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-200">
                I will pay later
            </a>

        </div>

    </section>

</div>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-copy-amount]').forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copyAmount);
                button.textContent = 'Copied! Paste it in GCash';
            } catch (error) {
                button.textContent = 'Type ' + button.dataset.copyAmount + ' in GCash';
            }
        });
    });

    const receiptInput = document.getElementById('receipt');
    const receiptPreview = document.getElementById('receipt-preview');

    if (receiptInput && receiptPreview) {
        receiptInput.addEventListener('change', () => {
            const file = receiptInput.files && receiptInput.files[0];

            if (!file || !file.type.startsWith('image/')) {
                receiptPreview.classList.add('hidden');
                return;
            }

            receiptPreview.src = URL.createObjectURL(file);
            receiptPreview.classList.remove('hidden');
        });
    }
</script>
@endpush
