@extends('layouts.landing')

@section('title', 'Services | Faith Community Loboc Parish')

@section('page')
<div class="page content-page bg-[#fffaf0]">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_12%_18%,rgba(216,171,49,.10),transparent_25%),radial-gradient(circle_at_88%_75%,rgba(18,61,103,.08),transparent_25%)]"></div>

    <header class="absolute top-0 left-0 right-0 z-40 px-4 md:px-8 pt-5 md:pt-6">
        <nav class="site-nav max-w-[1400px] mx-auto bg-white rounded-2xl nav-shadow px-5 lg:px-9 py-3">
            <div class="site-nav-row flex items-center justify-between gap-5">
                <a href="{{ route('landing') }}" class="site-logo flex items-center gap-3 shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="Faith Community Loboc Parish" class="h-12 md:h-14 w-auto">
                    <div class="leading-tight">
                        <h1 class="font-bold text-lg md:text-xl tracking-wide text-[#102d50]">FAITH COMMUNITY</h1>
                        <p class="font-bold text-lg md:text-xl tracking-wide text-[#102d50]">LOBOC PARISH</p>
                    </div>
                </a>
                <div class="site-nav-links desktop-nav flex items-center gap-6 lg:gap-8 text-sm lg:text-[15px] font-semibold">
                    <a href="{{ route('landing') }}" class="text-[#102d50]">Home</a>
                    <a href="{{ route('landing.about') }}" class="text-[#102d50]">About</a>
                    <a href="{{ route('landing.services') }}" class="text-[#102d50] border-b-2 border-[#d8ab31] pb-2">Services</a>
                    <a href="{{ route('landing.announcements') }}" class="text-[#102d50]">Announcements</a>
                    <a href="{{ route('landing.contact') }}" class="text-[#102d50]">Contact</a>
                </div>
                <div class="site-auth-links flex items-center gap-3 shrink-0">
                    <a href="{{ route('login') }}" class="site-auth-link border border-[#102d50] text-[#102d50] px-5 md:px-7 py-2.5 rounded-xl font-semibold hover:bg-[#102d50] hover:text-white transition">Log In</a>
                    <a href="{{ route('register') }}" class="site-auth-link bg-[#123d67] text-white px-5 md:px-8 py-2.5 rounded-xl font-semibold hover:bg-[#0d3154] transition">Register</a>
                </div>
            </div>
        </nav>
    </header>

    <main class="content-main relative z-10 max-w-6xl mx-auto px-6 md:px-10 pt-32 pb-8 flex items-center">
        <div class="w-full">
            <div class="text-center mb-7">
                <p class="text-sm tracking-[0.3em] font-semibold text-[#755416]">SERVICES</p>
                <h2 class="text-4xl md:text-5xl font-bold text-[#102d50] mt-2">Our Parish Services</h2>
                <div class="w-12 h-[3px] bg-[#d8ab31] mx-auto mt-4"></div>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
    <div class="bg-white rounded-2xl soft-card p-6"><div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center text-2xl">📅</div><h3 class="font-bold text-lg mt-4 text-[#102d50]">Mass Intentions</h3><p class="text-gray-600 text-sm mt-2 leading-relaxed">Submit Mass intentions online and choose an available parish schedule.</p></div>
    <div class="bg-white rounded-2xl soft-card p-6"><div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center text-2xl">🙏</div><h3 class="font-bold text-lg mt-4 text-[#102d50]">Prayer Requests</h3><p class="text-gray-600 text-sm mt-2 leading-relaxed">Send prayer requests and stay connected with parish prayer services.</p></div>
    <div class="bg-white rounded-2xl soft-card p-6"><div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center text-2xl">💚</div><h3 class="font-bold text-lg mt-4 text-[#102d50]">Online Donations</h3><p class="text-gray-600 text-sm mt-2 leading-relaxed">Support parish missions, activities, and programs through donations.</p></div>
    <div class="bg-white rounded-2xl soft-card p-6"><div class="w-14 h-14 rounded-full bg-rose-50 flex items-center justify-center text-2xl">📣</div><h3 class="font-bold text-lg mt-4 text-[#102d50]">Announcements</h3><p class="text-gray-600 text-sm mt-2 leading-relaxed">Stay informed about parish news, activities, schedules, and events.</p></div>
</div>
        </div>
    </main>
</div>
@endsection
