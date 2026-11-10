@extends('layouts.admin')

@section('title', 'Settings | Faith Community Platform')

@section('content')

<div class="max-w-5xl mx-auto">

    {{-- PAGE HEADER --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            Parish Settings
        </h1>

        <p class="text-gray-500 mt-2">
            Enter and manage the parish information used by the system.
        </p>
    </div>


    {{-- SUCCESS MESSAGE --}}
    @if(session('settings_success'))

        <div class="mb-6 p-4 rounded-lg bg-green-100 border border-green-300 text-green-800">

            {{ session('settings_success') }}

        </div>

    @endif


    {{-- VALIDATION ERRORS --}}
    @if($errors->any())

        <div class="mb-6 p-4 rounded-lg bg-red-100 border border-red-300 text-red-800">

            <p class="font-semibold mb-2">
                Please correct the following:
            </p>

            <ul class="list-disc ml-5">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- SETTINGS FORM --}}
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-8">

        <form
            method="POST"
            action="{{ route('admin.settings.update') }}"
        >

            @csrf

            @method('PUT')


            {{-- PARISH NAME --}}
            <div class="mb-6">

                <label
                    for="parish_name"
                    class="block text-sm font-semibold text-gray-700 mb-2"
                >
                    Parish Name
                </label>

                <input
                    type="text"
                    id="parish_name"
                    name="parish_name"
                    value="{{ old('parish_name') }}"
                    placeholder="Enter parish name"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"
                    required
                >

            </div>


            {{-- ADDRESS --}}
            <div class="mb-6">

                <label
                    for="address"
                    class="block text-sm font-semibold text-gray-700 mb-2"
                >
                    Parish Address
                </label>

                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    placeholder="Enter parish address"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"
                    required
                >{{ old('address') }}</textarea>

            </div>


            {{-- CONTACT NUMBER --}}
            <div class="mb-6">

                <label
                    for="contact_number"
                    class="block text-sm font-semibold text-gray-700 mb-2"
                >
                    Contact Number
                </label>

                <input
                    type="text"
                    id="contact_number"
                    name="contact_number"
                    value="{{ old('contact_number') }}"
                    placeholder="Enter contact number"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"
                    required
                >

            </div>


            {{-- EMAIL --}}
            <div class="mb-6">

                <label
                    for="email"
                    class="block text-sm font-semibold text-gray-700 mb-2"
                >
                    Parish Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Enter parish email"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"
                    required
                >

            </div>


            {{-- TIMEZONE --}}
            <div class="mb-8">

                <label
                    for="timezone"
                    class="block text-sm font-semibold text-gray-700 mb-2"
                >
                    Timezone
                </label>

                <select
                    id="timezone"
                    name="timezone"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"
                    required
                >

                    <option value="">
                        Select timezone
                    </option>

                    <option
                        value="Asia/Manila"
                        {{ old('timezone') == 'Asia/Manila' ? 'selected' : '' }}
                    >
                        Asia/Manila (Philippines)
                    </option>

                    <option
                        value="Asia/Tokyo"
                        {{ old('timezone') == 'Asia/Tokyo' ? 'selected' : '' }}
                    >
                        Asia/Tokyo
                    </option>

                    <option
                        value="UTC"
                        {{ old('timezone') == 'UTC' ? 'selected' : '' }}
                    >
                        UTC
                    </option>

                </select>

            </div>


            {{-- SAVE BUTTON --}}
            <div class="flex justify-end">

                <button
                    type="submit"
                    class="px-6 py-3 bg-purple-700 hover:bg-purple-800 text-white font-semibold rounded-lg transition"
                >
                    Save Settings
                </button>

            </div>

        </form>

    </div>


    {{-- INFORMATION --}}
    <div class="mt-6 p-5 bg-purple-50 border border-purple-200 rounded-xl">

        <h3 class="font-semibold text-purple-900 mb-2">
            Administrator Only
        </h3>

        <p class="text-sm text-purple-800">
            These settings can only be accessed and changed by an
            administrator. Parishioners cannot edit this information.
        </p>

    </div>

</div>

@endsection