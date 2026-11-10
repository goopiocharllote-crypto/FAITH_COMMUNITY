@extends('layouts.user')

@section('title', 'Donations | Faith Community Platform')
@section('page-title', 'Donations')

@section('content')
    <header class="mb-7">
        <h2 class="text-3xl font-bold text-slate-900">Donations</h2>
        <p class="mt-2 text-slate-600">Support our parish missions and programs — online through GCash or in person at the parish office.</p>
        <a href="{{ route('users.my-request') }}" class="mt-3 inline-flex text-sm font-semibold text-blue-800 hover:underline">View all my requests</a>
    </header>

    @include('user-components::donations')
@endsection
