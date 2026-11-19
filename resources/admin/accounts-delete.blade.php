@extends('layouts.admin')

@section('title', 'Delete Account | Admin')
@section('page-title', 'Delete Account')
@section('page-description', 'Confirm that you want to delete this parishioner account.')

@section('content')
    <section class="max-w-2xl overflow-hidden rounded-xl border border-rose-200 bg-white shadow-sm">
        <div class="border-b border-rose-100 bg-rose-50 px-5 py-4">
            <h2 class="text-lg font-bold text-rose-900">Delete {{ $account->name }}'s account?</h2>
            <p class="mt-1 text-sm text-rose-800">This cannot be undone from the admin portal.</p>
        </div>

        <div class="space-y-5 px-5 py-5 text-sm text-slate-700">
            <dl class="grid gap-2 rounded-lg bg-slate-50 p-4 sm:grid-cols-[8rem_1fr]">
                <dt class="font-semibold text-slate-600">Name</dt>
                <dd>{{ $account->name }}</dd>
                <dt class="font-semibold text-slate-600">Email</dt>
                <dd>{{ $account->email }}</dd>
                <dt class="font-semibold text-slate-600">Mobile</dt>
                <dd>{{ $account->mobile ? '0'.$account->mobile : '—' }}</dd>
                <dt class="font-semibold text-slate-600">Joined</dt>
                <dd>{{ $account->created_at?->format('M d, Y') ?? '—' }}</dd>
            </dl>

            <div>
                <p class="font-semibold text-slate-900">What happens when you delete this account</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>They are signed out and can no longer log in, with email, Google, or a password reset.</li>
                    <li>Their email address and mobile number are removed from the account, so they could register again later as a new parishioner.</li>
                    <li>
                        Their <strong>{{ $account->mass_intentions_count }}</strong> Mass {{ \Illuminate\Support\Str::plural('Intention', $account->mass_intentions_count) }},
                        <strong>{{ $account->prayer_requests_count }}</strong> prayer {{ \Illuminate\Support\Str::plural('request', $account->prayer_requests_count) }}
                        and <strong>{{ $account->donations_count }}</strong> {{ \Illuminate\Support\Str::plural('donation', $account->donations_count) }}
                        stay in the parish records under their name, including GCash payments and receipts.
                    </li>
                    <li>No more email or text updates are sent to them.</li>
                </ul>
                <p class="mt-3 text-slate-500">If they may come back, <strong>deactivate</strong> the account instead — it can be reactivated at any time.</p>
            </div>

            <form method="POST" action="{{ route('admin.accounts.destroy', $account) }}" class="space-y-3">
                @csrf
                @method('DELETE')
                <div>
                    <label for="confirm_name" class="block font-semibold text-slate-900">
                        Type <span class="font-mono text-rose-700">{{ $account->name }}</span> to confirm
                    </label>
                    <input
                        id="confirm_name"
                        name="confirm_name"
                        type="text"
                        autocomplete="off"
                        required
                        value="{{ old('confirm_name') }}"
                        class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-100"
                    >
                    @error('confirm_name')
                        <p role="alert" class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 focus:outline-none focus:ring-4 focus:ring-rose-200">Delete account</button>
                    <a href="{{ route('admin.accounts') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                </div>
            </form>
        </div>
    </section>
@endsection
