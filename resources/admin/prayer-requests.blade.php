@extends('layouts.admin')

@section('title', 'Prayer Requests | Admin')

@section('page-title', 'Prayer Requests')
@section('page-description', 'Review parishioner prayer requests')

@section('content')


<div
    class="
        bg-white
        border
        border-gray-200
        rounded-xl
        overflow-hidden
    "
>

    <div class="overflow-x-auto">

        <table class="w-full">

            <thead>

                <tr class="bg-gray-50">

                    <th class="text-left px-6 py-4">
                        User
                    </th>

                    <th class="text-left px-6 py-4">
                        Title
                    </th>

                    <th class="text-left px-6 py-4">
                        Prayer Request
                    </th>

                    <th class="text-left px-6 py-4">
                        Date
                    </th>

                    <th class="text-left px-6 py-4">
                        Status
                    </th>

                    <th class="text-left px-6 py-4">
                        Action
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($prayerRequests as $prayerRequest)

                    <tr class="border-t border-gray-100">


                        <td class="px-6 py-4">

                            {{ $prayerRequest->user->name ?? 'Unknown' }}

                        </td>


                        <td class="px-6 py-4 font-semibold">

                            {{ $prayerRequest->title }}

                        </td>


                        <td class="px-6 py-4 max-w-md">

                            <p class="line-clamp-3">

                                {{ $prayerRequest->request }}

                            </p>

                        </td>


                        <td class="px-6 py-4">

                            {{ $prayerRequest->created_at->format('M d, Y h:i A') }}

                        </td>


                        <td class="px-6 py-4">

                            @if ($prayerRequest->status === 'Approved')

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
                                    Approved
                                </span>

                            @elseif ($prayerRequest->status === 'Rejected')

                                <span
                                    class="
                                        px-3
                                        py-1
                                        rounded-full
                                        bg-red-100
                                        text-red-700
                                        text-sm
                                        font-semibold
                                    "
                                >
                                    Rejected
                                </span>

                            @else

                                <span
                                    class="
                                        px-3
                                        py-1
                                        rounded-full
                                        bg-yellow-100
                                        text-yellow-700
                                        text-sm
                                        font-semibold
                                    "
                                >
                                    Pending
                                </span>

                            @endif

                        </td>


                        <td class="px-6 py-4">

                            @if ($prayerRequest->status === 'Pending')

                                <div class="flex gap-2">


                                    <form
                                        method="POST"
                                        action="{{
                                            route(
                                                'admin.prayer-requests.approve',
                                                $prayerRequest
                                            )
                                        }}"
                                    >

                                        @csrf

                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="
                                                px-3
                                                py-2
                                                rounded-lg
                                                bg-green-600
                                                text-white
                                                text-sm
                                                font-semibold
                                                hover:bg-green-700
                                            "
                                        >
                                            Approve
                                        </button>

                                    </form>


                                    <form
                                        method="POST"
                                        action="{{
                                            route(
                                                'admin.prayer-requests.reject',
                                                $prayerRequest
                                            )
                                        }}"
                                    >

                                        @csrf

                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="
                                                px-3
                                                py-2
                                                rounded-lg
                                                bg-red-600
                                                text-white
                                                text-sm
                                                font-semibold
                                                hover:bg-red-700
                                            "
                                        >
                                            Reject
                                        </button>

                                    </form>

                                </div>

                            @else

                                <span class="text-gray-400 text-sm">
                                    No action
                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            class="
                                text-center
                                py-12
                                text-gray-500
                            "
                        >
                            No Prayer Requests yet.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection