@extends('layouts.user')

@section('title', 'My Mass Intentions | Faith Community Platform')

@section('page-title', 'My Mass Intentions')

@section('content')

<div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">

    <div>
        <h2 class="text-3xl text-gray-900">My Mass Intentions</h2>
        <p class="font-bold italic text-gray-600 mt-1">Your requests, reference numbers and GCash payments.</p>
    </div>

    <a href="{{ route('mass-intentions.index') }}" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-blue-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200">
        Request a Mass Intention
    </a>

</div>
<!-- ===================================================== -->
<!-- MY MASS INTENTIONS -->
<!-- ===================================================== -->

<div
    class="
        mt-0
        border
        border-gray-200
        rounded-xl
        overflow-hidden
        w-full
    "
>

    <div
        class="
            px-5
            py-4
            border-b
            border-gray-200
        "
    >

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-xl font-bold">My Mass Intentions</h3>
                <p class="mt-1 text-sm text-gray-600">Requests are sent to the parish administrator for review.</p>
            </div>
            <a href="{{ route('users.my-request') }}" class="font-semibold text-blue-700 underline hover:text-blue-900">
                View all request statuses
            </a>
        </div>

    </div>


    <div class="overflow-x-auto">

        <table class="w-full">

            <thead>

                <tr class="bg-gray-50">

                    <th scope="col" class="text-left px-5 py-4">
                        Reference No.
                    </th>

                    <th class="text-left px-5 py-4">
                        Intention For
                    </th>

                    <th class="text-left px-5 py-4">
                        Type
                    </th>

                    <th class="text-left px-5 py-4">
                        Date
                    </th>

                    <th class="text-left px-5 py-4">
                        Status
                    </th>

                    <th class="text-left px-5 py-4">
                        Offering
                    </th>

                    <th class="text-left px-5 py-4">
                        Payment
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse (
                    $massIntentions
                    as $massIntention
                )

                    <tr class="border-t border-gray-100">

                        <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                            <a href="{{ route('mass-intentions.confirmation', $massIntention) }}" class="font-semibold text-blue-700 underline hover:text-blue-900" title="View booking confirmation">{{ $massIntention->referenceNumber() }}</a>
                        </td>

                        <td class="px-5 py-4 font-semibold">
                            {{ $massIntention->intention_for }}
                            @if ($massIntention->additional_message)
                                <details class="mt-1 font-normal">
                                    <summary class="cursor-pointer text-xs text-blue-700 hover:text-blue-900">View message</summary>
                                    <p class="mt-1 max-w-xs whitespace-pre-line text-xs text-gray-600">{{ $massIntention->additional_message }}</p>
                                </details>
                            @endif
                        </td>


                        <td class="px-5 py-4">

                            {{ $massIntention->intention_type }}

                        </td>


                        <td class="px-5 py-4">

                            {{ $massIntention->mass_date->format('M d, Y h:i A') }}

                        </td>


                        <td class="px-5 py-4">
                            @include('user-components::request-status', [
                                'status' => $massIntention->status,
                                'churchReadAt' => $massIntention->church_read_at,
                            ])
                        </td>


                        <td class="px-5 py-4">

                            ₱{{ number_format(
                                $massIntention->offering_amount,
                                2
                            ) }}

                        </td>


                        <td class="px-5 py-4">
                            @include('user-components::payment-status', [
                                'payable' => $massIntention,
                                'payRoute' => route('payments.mass-intentions.pay', $massIntention),
                            ])
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="7"
                            class="
                                text-center
                                py-10
                                text-gray-500
                            "
                        >

                            No Mass Intentions submitted yet.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>



@endsection
