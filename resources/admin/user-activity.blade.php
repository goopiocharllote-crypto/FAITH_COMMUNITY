@extends('layouts.admin')

@section('title', 'User Activity | Admin')

@section('page-title', 'User Activity')
@section('page-description', 'Monitor parishioner login activity and currently active users')

@section('content')


<!-- ===================================================== -->
<!-- ACTIVE USERS -->
<!-- ===================================================== -->

<div
    class="
        bg-white
        border
        border-gray-200
        rounded-xl
        overflow-hidden
        mb-8
    "
>

    <div
        class="
            px-6
            py-5
            border-b
            border-gray-200
        "
    >

        <div class="flex items-center justify-between">

            <div>

                <h3 class="text-xl font-bold text-gray-900">
                    Currently Active Users
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Users active within the last 2 minutes.
                </p>

            </div>

            <div
                class="
                    px-4
                    py-2
                    rounded-full
                    bg-green-100
                    text-green-700
                    font-bold
                "
            >
                {{ $activeUsers->count() }} Active
            </div>

        </div>

    </div>


    <div class="overflow-x-auto">

        <table class="w-full">

            <thead>

                <tr class="bg-gray-50">

                    <th class="text-left px-6 py-4">
                        User
                    </th>

                    <th class="text-left px-6 py-4">
                        Email
                    </th>

                    <th class="text-left px-6 py-4">
                        Last Login
                    </th>

                    <th class="text-left px-6 py-4">
                        Last Activity
                    </th>

                    <th class="text-left px-6 py-4">
                        Status
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($activeUsers as $user)

                    <tr class="border-t border-gray-100">

                        <td class="px-6 py-4 font-semibold">
                            {{ $user->name }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $user->email }}
                        </td>

                        <td class="px-6 py-4">
                            {{
                                $user->last_login_at
                                    ? $user->last_login_at->format('M d, Y h:i A')
                                    : 'Never'
                            }}
                        </td>

                        <td class="px-6 py-4">
                            {{
                                $user->last_activity_at
                                    ? $user->last_activity_at->diffForHumans()
                                    : 'No activity'
                            }}
                        </td>

                        <td class="px-6 py-4">

                            <span
                                class="
                                    inline-flex
                                    items-center
                                    gap-2
                                    px-3
                                    py-1
                                    rounded-full
                                    bg-green-100
                                    text-green-700
                                    text-sm
                                    font-semibold
                                "
                            >

                                <span class="w-2 h-2 rounded-full bg-green-500"></span>

                                Online

                            </span>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="5"
                            class="
                                text-center
                                py-12
                                text-gray-500
                            "
                        >

                            No users are currently active.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


<!-- ===================================================== -->
<!-- ALL USERS -->
<!-- ===================================================== -->

<div
    class="
        bg-white
        border
        border-gray-200
        rounded-xl
        overflow-hidden
        mb-8
    "
>

    <div
        class="
            px-6
            py-5
            border-b
            border-gray-200
        "
    >

        <h3 class="text-xl font-bold">
            User Activity Overview
        </h3>

    </div>


    <div class="overflow-x-auto">

        <table class="w-full">

            <thead>

                <tr class="bg-gray-50">

                    <th class="text-left px-6 py-4">
                        User
                    </th>

                    <th class="text-left px-6 py-4">
                        Email
                    </th>

                    <th class="text-left px-6 py-4">
                        Last Login
                    </th>

                    <th class="text-left px-6 py-4">
                        Last Activity
                    </th>

                    <th class="text-left px-6 py-4">
                        Status
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($users as $user)

                    @php
                        $isActive =
                            $user->last_activity_at &&
                            $user->last_activity_at->greaterThanOrEqualTo(
                                now()->subMinutes(2)
                            );
                    @endphp

                    <tr class="border-t border-gray-100">

                        <td class="px-6 py-4 font-semibold">
                            {{ $user->name }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $user->email }}
                        </td>

                        <td class="px-6 py-4">

                            {{
                                $user->last_login_at
                                    ? $user->last_login_at->format('M d, Y h:i A')
                                    : 'Never'
                            }}

                        </td>

                        <td class="px-6 py-4">

                            {{
                                $user->last_activity_at
                                    ? $user->last_activity_at->diffForHumans()
                                    : 'Never'
                            }}

                        </td>

                        <td class="px-6 py-4">

                            @if ($isActive)

                                <span
                                    class="
                                        px-3
                                        py-1
                                        rounded-full
                                        bg-green-100
                                        text-green-700
                                        text-sm
                                        font-semibold
                                    "
                                >
                                    Online
                                </span>

                            @else

                                <span
                                    class="
                                        px-3
                                        py-1
                                        rounded-full
                                        bg-gray-100
                                        text-gray-600
                                        text-sm
                                        font-semibold
                                    "
                                >
                                    Offline
                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="5"
                            class="
                                text-center
                                py-12
                                text-gray-500
                            "
                        >
                            No users found.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


<!-- ===================================================== -->
<!-- LOGIN HISTORY -->
<!-- ===================================================== -->

<div
    class="
        bg-white
        border
        border-gray-200
        rounded-xl
        overflow-hidden
    "
>

    <div
        class="
            px-6
            py-5
            border-b
            border-gray-200
        "
    >

        <h3 class="text-xl font-bold">
            Login History
        </h3>

        <p class="text-sm text-gray-500 mt-1">
            Recent user and administrator login records.
        </p>

    </div>


    <div class="overflow-x-auto">

        <table class="w-full">

            <thead>

                <tr class="bg-gray-50">

                    <th class="text-left px-6 py-4">
                        User
                    </th>

                    <th class="text-left px-6 py-4">
                        Login Time
                    </th>

                    <th class="text-left px-6 py-4">
                        Logout Time
                    </th>

                    <th class="text-left px-6 py-4">
                        IP Address
                    </th>

                    <th class="text-left px-6 py-4">
                        Status
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($loginHistory as $log)

                    <tr class="border-t border-gray-100">

                        <td class="px-6 py-4 font-semibold">

                            {{ $log->user->name ?? 'Unknown' }}

                        </td>

                        <td class="px-6 py-4">

                            {{ $log->login_at->format('M d, Y h:i A') }}

                        </td>

                        <td class="px-6 py-4">

                            @if ($log->logout_at)

                                {{ $log->logout_at->format('M d, Y h:i A') }}

                            @else

                                —

                            @endif

                        </td>

                        <td class="px-6 py-4">

                            {{ $log->ip_address ?? 'Unknown' }}

                        </td>

                        <td class="px-6 py-4">

                            @if (!$log->logout_at)

                                <span
                                    class="
                                        px-3
                                        py-1
                                        rounded-full
                                        bg-green-100
                                        text-green-700
                                        text-sm
                                        font-semibold
                                    "
                                >
                                    Active Session
                                </span>

                            @else

                                <span
                                    class="
                                        px-3
                                        py-1
                                        rounded-full
                                        bg-gray-100
                                        text-gray-600
                                        text-sm
                                        font-semibold
                                    "
                                >
                                    Logged Out
                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="5"
                            class="
                                text-center
                                py-12
                                text-gray-500
                            "
                        >

                            No login history yet.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection