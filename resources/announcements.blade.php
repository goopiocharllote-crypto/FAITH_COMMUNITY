@extends('layouts.landing')

@section('title', 'Announcements | Faith Community Loboc Parish')

@section('page')
<div class="page content-page bg-[#fffaf0]">
    <header class="absolute top-0 left-0 right-0 z-40 px-4 md:px-8 pt-5 md:pt-6">
        <nav class="site-nav max-w-[1400px] mx-auto bg-white rounded-2xl nav-shadow px-5 lg:px-9 py-3">
            <div class="site-nav-row flex items-center justify-between gap-5">
                <a href="{{ route('landing') }}" class="site-logo flex items-center gap-3 shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="Faith Community Loboc Parish" class="h-12 md:h-14 w-auto">
                    <div class="leading-tight"><h1 class="font-bold text-lg md:text-xl tracking-wide text-[#102d50]">FAITH COMMUNITY</h1><p class="font-bold text-lg md:text-xl tracking-wide text-[#102d50]">LOBOC PARISH</p></div>
                </a>
                <div class="site-nav-links desktop-nav flex items-center gap-6 lg:gap-8 text-sm lg:text-[15px] font-semibold">
                    <a href="{{ route('landing') }}" class="text-[#102d50]">Home</a>
                    <a href="{{ route('landing.about') }}" class="text-[#102d50]">About</a>
                    <a href="{{ route('landing.services') }}" class="text-[#102d50]">Services</a>
                    <a href="{{ route('landing.announcements') }}" class="text-[#102d50] border-b-2 border-[#d8ab31] pb-2">Announcements</a>
                    <a href="{{ route('landing.contact') }}" class="text-[#102d50]">Contact</a>
                </div>
                <div class="site-auth-links flex items-center gap-3 shrink-0"><a href="{{ route('login') }}" class="site-auth-link border border-[#102d50] text-[#102d50] px-5 md:px-7 py-2.5 rounded-xl font-semibold">Log In</a><a href="{{ route('register') }}" class="site-auth-link bg-[#123d67] text-white px-5 md:px-8 py-2.5 rounded-xl font-semibold">Register</a></div>
            </div>
        </nav>
    </header>

    <main class="content-main relative z-10 max-w-6xl mx-auto px-6 md:px-10 pt-32 pb-8 flex flex-col">
        <div class="text-center mb-6"><p class="text-sm tracking-[0.3em] font-semibold text-[#755416]">STAY CONNECTED</p><h2 class="text-4xl md:text-5xl font-bold text-[#102d50] mt-2">Parish Announcements</h2><div class="w-12 h-[3px] bg-[#d8ab31] mx-auto mt-4"></div></div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @forelse ($announcements as $announcement)
                <article class="bg-white rounded-2xl soft-card p-6 overflow-hidden">
                    <div class="flex items-center gap-2 text-xs font-semibold text-[#755416] uppercase tracking-wider">
                        <span>{{ optional($announcement->announcement_date ?? $announcement->created_at)->format('F d, Y') }}</span>
                    </div>
                    <h3 class="text-xl font-bold text-[#102d50] mt-3">{{ $announcement->title }}</h3>
                    <p class="text-gray-600 mt-3 leading-relaxed text-sm">{{ \Illuminate\Support\Str::limit($announcement->content, 230) }}</p>
                </article>
            @empty
                <div class="md:col-span-3 bg-white rounded-2xl soft-card flex items-center justify-center p-10 text-center">
                    <div><div class="text-5xl">📣</div><h3 class="text-2xl font-bold text-[#102d50] mt-4">No announcements available yet.</h3><p class="text-gray-600 mt-2">Please check again later.</p></div>
                </div>
            @endforelse
        </div>
    </main>
</div>
@endsection
