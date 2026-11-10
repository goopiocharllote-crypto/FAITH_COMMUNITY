@extends('layouts.user')

@section('title', 'My Request | Faith Community Platform')
@section('page-title', 'My Request')

@section('content')
    <header class="mb-8">
        <h2 class="text-3xl font-bold text-slate-900">My Requests</h2>
        <p class="mt-2 text-slate-600">Submit parish requests and follow their status in one place.</p>
        <nav class="mt-5 flex flex-wrap gap-2" aria-label="Request sections">
            <a href="#mass-intentions" class="rounded-lg bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-800 hover:bg-blue-100">Mass Intentions</a>
            <a href="#prayer-requests" class="rounded-lg bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-800 hover:bg-blue-100">Prayer Requests</a>
            <a href="#donations" class="rounded-lg bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-800 hover:bg-blue-100">Donations</a>
        </nav>
    </header>

    <div class="space-y-8">
        @include('user-components::mass-intentions')
        @include('user-components::prayer-request')
        @include('user-components::donations')
    </div>
@endsection
