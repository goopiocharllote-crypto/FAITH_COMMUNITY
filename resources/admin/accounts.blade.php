@extends('layouts.admin')

@section('title', 'Manage Accounts | Admin')
@section('page-title', 'Manage Accounts')
@section('page-description', 'Search parishioner accounts, deactivate or reactivate them, and delete accounts that are no longer needed.')

@section('content')
    @if (session('accounts_success'))
        <div role="status" aria-live="polite" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-semibold text-emerald-800">
            {{ session('accounts_success') }}
        </div>
    @endif

    @if ($errors->has('accounts'))
        <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 font-semibold text-red-800">
            {{ $errors->first('accounts') }}
        </div>
    @endif

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('admin.accounts', array_filter(['search' => $search])) }}" class="rounded-xl border bg-white p-5 shadow-sm transition hover:border-blue-300">
            <p class="text-sm font-medium text-slate-500">All accounts</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $counts['all'] }}</p>
        </a>
        <a href="{{ route('admin.accounts', array_filter(['status' => 'active', 'search' => $search])) }}" class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm transition hover:border-emerald-400">
            <p class="text-sm font-medium text-emerald-800">Active</p>
            <p class="mt-2 text-3xl font-bold text-emerald-900">{{ $counts['active'] }}</p>
        </a>
        <a href="{{ route('admin.accounts', array_filter(['status' => 'deactivated', 'search' => $search])) }}" class="rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm transition hover:border-rose-400">
            <p class="text-sm font-medium text-rose-800">Deactivated</p>
            <p class="mt-2 text-3xl font-bold text-rose-900">{{ $counts['deactivated'] }}</p>
        </a>
    </div>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">
                    {{ $activeStatus ? ucfirst($activeStatus).' accounts' : 'All accounts' }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">Deactivated parishioners are signed out and cannot log in until reactivated.</p>
            </div>
            <form method="GET" action="{{ route('admin.accounts') }}" role="search" class="flex w-full gap-2 lg:w-auto">
                @if ($activeStatus)
                    <input type="hidden" name="status" value="{{ $activeStatus }}">
                @endif
                <label for="account-search" class="sr-only">Search accounts</label>
                <input
                    id="account-search"
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Name, email or mobile"
                    class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100 lg:w-72"
                >
                <button type="submit" class="rounded-lg bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 focus:outline-none focus:ring-4 focus:ring-sky-200">Search</button>
                @if ($search !== '')
                    <a href="{{ route('admin.accounts', array_filter(['status' => $activeStatus])) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="border-b border-rose-100 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th scope="col" class="px-5 py-4">Name</th>
                        <th scope="col" class="px-5 py-4">Email</th>
                        <th scope="col" class="px-5 py-4">Mobile</th>
                        <th scope="col" class="px-5 py-4">Status</th>
                        <th scope="col" class="px-5 py-4">Joined</th>
                        <th scope="col" class="px-5 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rose-100">
                    @forelse ($users as $account)
                        @php($isProtected = $account->isAdministrator() || $account->is(auth()->user()))
                        <tr class="align-top transition hover:bg-slate-50/70">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-900">{{ $account->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    @if ($account->isAdministrator())
                                        Administrator{{ $account->is(auth()->user()) ? ' · you' : '' }}
                                    @else
                                        {{ $account->mass_intentions_count }} Mass {{ \Illuminate\Support\Str::plural('Intention', $account->mass_intentions_count) }}
                                        · {{ $account->prayer_requests_count }} prayer {{ \Illuminate\Support\Str::plural('request', $account->prayer_requests_count) }}
                                        · {{ $account->donations_count }} {{ \Illuminate\Support\Str::plural('donation', $account->donations_count) }}
                                    @endif
                                </p>
                            </td>
                            <td class="px-5 py-4 text-slate-700">{{ $account->email }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-700">{{ $account->mobile ? '0'.$account->mobile : '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-4">
                                @if ($account->isDeactivated())
                                    <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800">Deactivated</span>
                                    <p class="mt-1 text-xs text-slate-500">since {{ $account->deactivated_at->format('M d, Y') }}</p>
                                @else
                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Active</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-700">
                                {{ $account->created_at?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-4">
                                @if ($isProtected)
                                    <span class="text-xs text-slate-400">No action available</span>
                                @else
                                    <div class="flex flex-wrap gap-2">
                                        @if ($account->isDeactivated())
                                            <form method="POST" action="{{ route('admin.accounts.reactivate', $account) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">Reactivate</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.accounts.deactivate', $account) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from('Deactivate '.$account->name.'? They will be signed out and unable to log in until reactivated.') }})">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg border border-amber-500 px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-50 focus:outline-none focus:ring-4 focus:ring-amber-100">Deactivate</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.accounts.delete', $account) }}" class="rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700 focus:outline-none focus:ring-4 focus:ring-rose-200">Delete</a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <p class="font-semibold text-slate-700">
                                    {{ $search !== '' ? 'No accounts match “'.$search.'”.' : 'No accounts found.' }}
                                </p>
                                @if ($search !== '' || $activeStatus)
                                    <a href="{{ route('admin.accounts') }}" class="mt-3 inline-block text-sm font-semibold text-sky-700 hover:underline">View all accounts</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">
                {{ $users->links() }}
            </div>
        @endif
    </section>
@endsection
