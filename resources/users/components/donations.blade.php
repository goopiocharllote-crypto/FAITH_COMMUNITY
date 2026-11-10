<section id="donations" class="scroll-mt-6 space-y-5">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h3 class="text-xl font-bold text-slate-900">Make a Donation</h3>
        <p class="mt-1 text-sm text-slate-600">Give online through GCash, or pledge now and give at the parish office. You will get an email and SMS confirmation.</p>

        <form method="POST" action="{{ route('donations.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
            @csrf

            <fieldset class="md:col-span-2">
                <legend class="mb-2 block text-sm font-semibold text-slate-700">How would you like to give?</legend>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-300 p-4 transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 has-[:checked]:ring-2 has-[:checked]:ring-blue-100">
                        <input type="radio" name="payment_method" value="GCash" required class="mt-1 h-4 w-4 accent-blue-700" @checked(old('payment_method', 'GCash') === 'GCash')>
                        <span>
                            <span class="block font-semibold text-slate-900">Donate online · GCash</span>
                            <span class="mt-0.5 block text-sm text-slate-600">Pay securely now. Minimum ₱{{ number_format((float) config('payments.minimum_online_amount'), 2) }}. Confirmed automatically.</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-300 p-4 transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 has-[:checked]:ring-2 has-[:checked]:ring-blue-100">
                        <input type="radio" name="payment_method" value="Cash" required class="mt-1 h-4 w-4 accent-blue-700" @checked(old('payment_method') === 'Cash')>
                        <span>
                            <span class="block font-semibold text-slate-900">Donate at the parish</span>
                            <span class="mt-0.5 block text-sm text-slate-600">Pledge now and give cash at the parish office. The office confirms once received.</span>
                        </span>
                    </label>
                </div>
                @error('payment_method')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </fieldset>

            <div>
                <label for="donation-amount" class="mb-1.5 block text-sm font-semibold text-slate-700">Amount (PHP)</label>
                <input id="donation-amount" name="amount" type="number" min="1" max="99999999.99" step="0.01" required value="{{ old('amount') }}" aria-describedby="@error('amount') donation-amount-error @enderror" class="w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">
                @error('amount')
                    <p id="donation-amount-error" class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
                @error('reference_number')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="donation-message" class="mb-1.5 block text-sm font-semibold text-slate-700">Note <span class="font-normal text-slate-500">(optional)</span></label>
                <input id="donation-message" name="message" type="text" maxlength="1000" value="{{ old('message') }}" placeholder="e.g. For the church restoration fund" class="w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">
                @error('message')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="rounded-lg bg-blue-900 px-5 py-3 font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200">
                    Continue
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h3 class="text-lg font-bold text-slate-900">Your Donations</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="px-5 py-3">Reference</th>
                        <th scope="col" class="px-5 py-3">Amount</th>
                        <th scope="col" class="px-5 py-3">Method</th>
                        <th scope="col" class="px-5 py-3">Submitted</th>
                        <th scope="col" class="px-5 py-3">Payment</th>
                        <th scope="col" class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($donations as $donation)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $donation->paymentReference() }}</td>
                            <td class="px-5 py-4 font-semibold text-slate-900">₱{{ number_format((float) $donation->amount, 2) }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $donation->isOnline() ? 'GCash (online)' : 'At the parish' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $donation->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-4">@include('user-components::payment-status', ['payable' => $donation, 'payRoute' => route('payments.donations.pay', $donation)])</td>
                            <td class="px-5 py-4">@include('user-components::request-status', ['status' => $donation->status, 'approvedLabel' => 'Received'])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-500">You have not made any donations yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
