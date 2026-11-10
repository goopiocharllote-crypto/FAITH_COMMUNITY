@extends('layouts.user')

@section('title', 'Profile | Faith Community Platform')
@section('page-title', 'Profile')

@section('content')
    <header class="mb-7">
        <h2 class="text-3xl text-gray-900">
            My Profile
        </h2>
        <p class="mt-1 text-gray-600">
            View your account information.
        </p>
    </header>

    <section class="max-w-3xl rounded-2xl border border-gray-200 p-7" aria-label="Profile information">
        <div class="flex items-center gap-5 border-b border-gray-200 pb-6">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-800 text-3xl font-bold text-white" aria-hidden="true">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>

            <div>
                <h3 class="text-2xl font-bold">
                    {{ Auth::user()->name }}
                </h3>
                <p class="text-gray-500">
                    Parishioner
                </p>
            </div>
        </div>

        <dl class="mt-6 space-y-5">
            <div>
                <dt class="text-sm text-gray-500">Full Name</dt>
                <dd class="text-lg font-semibold text-gray-900">
                    {{ Auth::user()->name }}
                </dd>
            </div>

            <div>
                <dt class="text-sm text-gray-500">Email</dt>
                <dd class="text-lg font-semibold text-gray-900">
                    {{ Auth::user()->email }}
                </dd>
            </div>

            <div>
                <dt class="text-sm text-gray-500">Account Type</dt>
                <dd class="text-lg font-semibold text-gray-900">
                    Parishioner
                </dd>
            </div>

            <div>
                <dt class="text-sm text-gray-500">Member Since</dt>
                <dd class="text-lg font-semibold text-gray-900">
                    {{ Auth::user()->created_at->format('F d, Y') }}
                </dd>
            </div>
        </dl>
    </section>
@endsection
