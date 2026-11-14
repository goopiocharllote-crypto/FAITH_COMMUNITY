@extends('layouts.admin')

@section('title', 'Donations | Admin')
    

@section('page-title', 'Donations')
@section('page-description', 'Review parishioner donation submissions')

@section('content')



<!-- TOTAL -->

<div
    class="
        bg-white
        border
        border-gray-200
        rounded-xl
        p-6
        mb-6
    "
>

    <p class="text-gray-500 text-sm">
        Total Donations
    </p>

    <p class="text-3xl font-bold text-green-600 mt-2">

        ₱{{ number_format($totalDonations, 2) }}

    </p>

</div>


<!-- DONATIONS TABLE -->

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
                        Amount
                    </th>

                    <th class="text-left px-6 py-4">
                        Payment Method
                    </th>

                    <th class="text-left px-6 py-4">
                        Reference
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

                @forelse ($donations as $donation)

                    <tr class="border-t border-gray-100">


                        <td class="px-6 py-4">

                            {{ $donation->user->name ?? 'Unknown' }}

                        </td>


                        <td class="px-6 py-4 font-bold text-green-600">

                            ₱{{ number_format($donation->amount, 2) }}

                        </td>


                        <td class="px-6 py-4">

                            {{ $donation->isOnline() ? 'GCash (QR code)' : 'At parish' }}
                            <p class="mt-1 text-xs font-semibold {{ $donation->isPaid() ? 'text-emerald-700' : ($donation->isPaymentSubmitted() ? 'text-sky-700' : 'text-amber-700') }}">
                                {{ $donation->isPaid() ? 'Paid' : ($donation->isOnline() ? ($donation->isPaymentSubmitted() ? 'GCash reference submitted · verify, then approve' : 'Awaiting GCash payment') : 'Not yet received') }}
                            </p>

                        </td>


                        <td class="px-6 py-4">

                            {{ $donation->paymentReference() }}
                            @php($gcashPayment = $donation->payments->whereIn('status', ['submitted', 'paid'])->last())
                            @if ($gcashPayment)
                                <p class="mt-1 text-xs text-slate-500">GCash ref. no.: {{ $gcashPayment->gateway_payment_id }}</p>
                            @elseif ($donation->reference_number)
                                <p class="mt-1 text-xs text-slate-500">{{ $donation->reference_number }}</p>
                            @endif

                        </td>


                        <td class="px-6 py-4">

                            {{ $donation->created_at->format('M d, Y h:i A') }}

                        </td>


                        <td class="px-6 py-4">

                            @if ($donation->status === 'Approved')

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

                            @elseif ($donation->status === 'Rejected')

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

                            @if ($donation->status === 'Pending')

                                <div class="flex gap-2">


                                    <form
                                        method="POST"
                                        action="{{
                                            route(
                                                'admin.donations.approve',
                                                $donation
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
                                                'admin.donations.reject',
                                                $donation
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
                            colspan="7"
                            class="
                                text-center
                                py-12
                                text-gray-500
                            "
                        >
                            No Donations yet.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection