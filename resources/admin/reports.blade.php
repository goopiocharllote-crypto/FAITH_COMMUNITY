@extends('layouts.admin')

@section('title', 'Reports | Admin')

@section('header-content')

<h1 class="text-4xl text-gray-900">
    Reports
</h1>

@endsection


@section('content')

<div class="space-y-6">

    {{-- FILTER --}}

    <div class="bg-white border border-[#dfd0d0] rounded-xl p-5">

        <form
            method="GET"
            action="{{ route('admin.reports') }}"
        >

            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 items-end">

                <div>

                    <label class="block text-sm font-semibold mb-2">
                        Report Type
                    </label>

                    <select
                        name="report_type"
                        class="w-full border-[#dfd0d0] rounded-lg"
                    >

                        <option
                            value="mass_intention"
                            {{ $reportType === 'mass_intention' ? 'selected' : '' }}
                        >
                            Mass Intention Report
                        </option>

                        <option
                            value="donation"
                            {{ $reportType === 'donation' ? 'selected' : '' }}
                        >
                            Donation Report
                        </option>

                        <option
                            value="prayer_request"
                            {{ $reportType === 'prayer_request' ? 'selected' : '' }}
                        >
                            Prayer Request Report
                        </option>

                    </select>

                </div>


                <div>

                    <label class="block text-sm font-semibold mb-2">
                        Date Range
                    </label>

                    <input
                        type="date"
                        name="start_date"
                        value="{{ $startDate }}"
                        class="w-full border-[#dfd0d0] rounded-lg"
                    >

                </div>


                <div>

                    <label class="block text-sm font-semibold mb-2">
                        End Date
                    </label>

                    <input
                        type="date"
                        name="end_date"
                        value="{{ $endDate }}"
                        class="w-full border-[#dfd0d0] rounded-lg"
                    >

                </div>


                <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg"
                >
                    Generate Report
                </button>

            </div>

        </form>

    </div>


    {{-- EXPORT BUTTONS --}}

    <div class="flex justify-end gap-3">

        <button
            type="button"
            onclick="window.print()"
            class="border border-red-400 text-red-500 px-5 py-2 rounded-lg hover:bg-red-50"
        >
            Export PDF
        </button>


        <button
            type="button"
            onclick="exportReport()"
            class="border border-green-500 text-green-600 px-5 py-2 rounded-lg hover:bg-green-50"
        >
            Export Excel
        </button>

    </div>


    {{-- SUMMARY --}}

    <div>

        <h2 class="font-bold text-lg mb-4">
            Report Summary
        </h2>


        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">

            <div
                class="
                    bg-[#fffafa]
                    border
                    border-[#dfd0d0]
                    rounded-2xl
                    p-5
                    min-h-[140px]
                    relative
                "
            >

                <p class="font-bold text-lg">
                    Total Mass Intention
                </p>

                <p class="text-4xl font-bold mt-3">
                    {{ $totalMassIntentions }}
                </p>

                <p class="text-sm mt-4">
                    This Month
                </p>

                <div class="absolute right-5 bottom-3 text-5xl text-orange-500">
                    +
                </div>

            </div>


            <div
                class="
                    bg-[#fffafa]
                    border
                    border-[#dfd0d0]
                    rounded-2xl
                    p-5
                    min-h-[140px]
                    relative
                "
            >

                <p class="font-bold text-lg">
                    Pending Requests
                </p>

                <p class="text-4xl font-bold mt-3">
                    {{ $pendingRequests }}
                </p>

                <p class="text-sm mt-4">
                    Needs Action
                </p>

                <div class="absolute right-5 bottom-3 text-4xl">
                    📋
                </div>

            </div>


            <div
                class="
                    bg-[#fffafa]
                    border
                    border-[#dfd0d0]
                    rounded-2xl
                    p-5
                    min-h-[140px]
                    relative
                "
            >

                <p class="font-bold text-lg">
                    Approved Requests
                </p>

                <p class="text-4xl font-bold mt-3">
                    {{ $approvedRequests }}
                </p>

                <p class="text-sm mt-4">
                    This Month
                </p>

                <div class="absolute right-5 bottom-3 text-4xl">
                    📋
                </div>

            </div>


            <div
                class="
                    bg-[#fffafa]
                    border
                    border-[#dfd0d0]
                    rounded-2xl
                    p-5
                    min-h-[140px]
                    relative
                "
            >

                <p class="font-bold text-lg">
                    Total Donations
                </p>

                <p class="text-3xl font-bold mt-4">
                    ₱{{ number_format($totalDonations, 2) }}
                </p>

                <p class="text-sm mt-4">
                    This Month
                </p>

                <div class="absolute right-5 bottom-3 text-4xl">
                    🙏
                </div>

            </div>

        </div>

    </div>


    {{-- LOWER CONTENT --}}

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

        {{-- TOP INTENTIONS --}}

        <section
            class="
                bg-[#fffafa]
                border
                border-[#dfd0d0]
                rounded-2xl
                p-5
            "
        >

            <h2 class="text-xl font-bold mb-6">
                Top Intentions
            </h2>


            <div class="space-y-5">

                @foreach($topIntentions as $intention)

                    <div>

                        <div class="flex justify-between mb-2">

                            <span>
                                {{ $intention['type'] }}
                            </span>

                            <span>
                                {{ $intention['percentage'] }}%
                            </span>

                        </div>


                        <div class="h-3 bg-gray-200 rounded-full">

                            <div
                                class="h-3 bg-blue-500 rounded-full"
                                style="
                                    width:
                                    {{ $intention['percentage'] }}%;
                                "
                            ></div>

                        </div>

                    </div>

                @endforeach

            </div>

        </section>


        {{-- INTENTION PER WEEK --}}

        <section
            class="
                bg-[#fffafa]
                border
                border-[#dfd0d0]
                rounded-2xl
                p-5
            "
        >

            <h2 class="text-xl font-bold mb-6">
                Intention Per Week
            </h2>


            @forelse($weeklyData as $week => $count)

                <div class="mb-5">

                    <div class="flex justify-between mb-2">

                        <span>
                            {{ $week }}
                        </span>

                        <strong>
                            {{ $count }}
                        </strong>

                    </div>


                    <div class="h-7 bg-gray-100 rounded-lg overflow-hidden">

                        <div
                            class="h-full bg-blue-500 rounded-lg"
                            style="
                                width:
                                {{
                                    min(
                                        ($count /
                                        max($totalMassIntentions, 1))
                                        * 100,
                                        100
                                    )
                                }}%;
                            "
                        ></div>

                    </div>

                </div>

            @empty

                <p class="text-gray-500">
                    No intentions found for this date range.
                </p>

            @endforelse

        </section>

    </div>


    {{-- DETAILS --}}

    <section
        id="reportTable"
        class="
            bg-[#fffafa]
            border
            border-[#dfd0d0]
            rounded-2xl
            p-5
        "
    >

        <h2 class="text-xl font-bold mb-5">
            Report Details
        </h2>


        <div class="overflow-x-auto">

            <table class="w-full border-collapse">

                <thead>

                    <tr>

                        <th class="border border-gray-300 p-3 text-left">
                            Intention
                        </th>

                        <th class="border border-gray-300 p-3">
                            Count
                        </th>

                        <th class="border border-gray-300 p-3">
                            Percentage
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach($topIntentions as $intention)

                        <tr>

                            <td class="border border-gray-300 p-3">
                                {{ $intention['type'] }}
                            </td>

                            <td class="border border-gray-300 p-3 text-center">
                                {{ $intention['count'] }}
                            </td>

                            <td class="border border-gray-300 p-3 text-center">
                                {{ $intention['percentage'] }}%
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </section>

</div>


<script>

function exportReport()
{
    const table =
        document.getElementById('reportTable');

    const rows =
        table.querySelectorAll('tr');

    let csv = '';

    rows.forEach(function(row) {

        const cells =
            row.querySelectorAll('th, td');

        const data = [];

        cells.forEach(function(cell) {

            data.push(
                '"' +
                cell.innerText
                    .replace(/"/g, '""') +
                '"'
            );

        });

        csv += data.join(',') + '\n';

    });


    const blob =
        new Blob(
            [csv],
            {
                type: 'text/csv;charset=utf-8;'
            }
        );


    const url =
        URL.createObjectURL(blob);


    const link =
        document.createElement('a');

    link.href = url;

    link.download =
        'loboc-parish-report.csv';

    document.body.appendChild(link);

    link.click();

    document.body.removeChild(link);

    URL.revokeObjectURL(url);
}

</script>


<style>

@media print {

    body * {
        visibility: hidden;
    }

    #reportTable,
    #reportTable * {
        visibility: visible;
    }

    #reportTable {

        position: absolute;

        left: 0;

        top: 0;

        width: 100%;

        border: none;

    }

}

</style>

@endsection