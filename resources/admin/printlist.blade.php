@extends('layouts.admin')

@section('title', 'Printlist | Admin')

@section('header-content')

<h1 class="text-4xl text-gray-900">
    Mass Intention List (Printable)
</h1>

@endsection


@section('content')

<div class="space-y-4">

    {{-- TOP CONTROLS --}}

    <div class="flex items-center justify-end gap-4">

        {{-- SHARE --}}

        <button
            type="button"
            onclick="sharePrintlist()"
            class="text-2xl hover:text-blue-600"
            title="Share"
        >
            ↗
        </button>


        {{-- PRINT --}}

        <button
            type="button"
            onclick="window.print()"
            class="text-2xl hover:text-blue-600"
            title="Print"
        >
            🖨
        </button>


        {{-- CATEGORY --}}

        <form
            method="GET"
            action="{{ route('admin.printlist') }}"
        >

            <select
                name="category"
                onchange="this.form.submit()"
                class="
                    rounded-xl
                    border-[#dfd0d0]
                    px-4
                    py-2
                    bg-white
                "
            >

                <option
                    value="All"
                    {{ $category === 'All'
                        ? 'selected'
                        : '' }}
                >
                    Category
                </option>

                <option
                    value="Thanksgiving"
                    {{ $category === 'Thanksgiving'
                        ? 'selected'
                        : '' }}
                >
                    Thanksgiving
                </option>

                <option
                    value="Healing"
                    {{ $category === 'Healing'
                        ? 'selected'
                        : '' }}
                >
                    Healing
                </option>

                <option
                    value="Souls"
                    {{ $category === 'Souls'
                        ? 'selected'
                        : '' }}
                >
                    Souls
                </option>

                <option
                    value="Birthday"
                    {{ $category === 'Birthday'
                        ? 'selected'
                        : '' }}
                >
                    Birthday
                </option>

                <option
                    value="Special Prayer"
                    {{ $category === 'Special Prayer'
                        ? 'selected'
                        : '' }}
                >
                    Special Prayer
                </option>

            </select>

        </form>

    </div>


    {{-- VIEW ALL --}}

    <div class="text-right">

        <a
            href="{{ route(
                'admin.printlist',
                ['category' => 'All']
            ) }}"
            class="
                text-blue-600
                font-semibold
                hover:underline
            "
        >
            View All
        </a>

    </div>


    {{-- PRINTABLE CONTAINER --}}

    <div
        id="printArea"
        class="
            bg-white
            border
            border-[#dfd0d0]
            rounded-xl
            p-5
        "
    >

        {{-- PARISH HEADER --}}

        <div class="text-center mb-5">

            <div class="text-5xl font-bold">
                ✚
            </div>

            <h2
                class="
                    text-2xl
                    font-bold
                    tracking-[0.3em]
                "
            >
                LOBOC PARISH
            </h2>

            <h3
                class="
                    text-xl
                    font-bold
                    tracking-[0.25em]
                "
            >
                MASS INTENTION LIST
            </h3>

        </div>


        <p class="font-medium mb-2">
            AFTERNOON MASS 5:00 PM ENGLISH MASS
        </p>


        {{-- TABLE --}}

        <div class="overflow-x-auto">

            <table
                class="
                    w-full
                    border-collapse
                    text-sm
                "
            >

                <thead>

                    <tr>

                        <th
                            class="
                                border
                                border-gray-300
                                px-3
                                py-3
                                text-center
                                w-12
                            "
                        >
                            #
                        </th>

                        <th
                            class="
                                border
                                border-gray-300
                                px-3
                                py-3
                                text-left
                            "
                        >
                            NAME
                        </th>

                        <th
                            class="
                                border
                                border-gray-300
                                px-3
                                py-3
                                text-left
                            "
                        >
                            Additional Message
                        </th>

                        <th
                            class="
                                border
                                border-gray-300
                                px-3
                                py-3
                                text-left
                            "
                        >
                            INTENTION
                        </th>

                        <th
                            class="
                                border
                                border-gray-300
                                px-3
                                py-3
                                text-right
                            "
                        >
                            OFFERING
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse(
                        $massIntentions
                        as $index => $massIntention
                    )

                        <tr>

                            <td
                                class="
                                    border
                                    border-gray-300
                                    px-3
                                    py-3
                                    text-center
                                "
                            >
                                {{ $index + 1 }}
                            </td>


                            <td
                                class="
                                    border
                                    border-gray-300
                                    px-3
                                    py-3
                                "
                            >
                                {{ $massIntention->intention_for }}
                            </td>


                            <td
                                class="
                                    border
                                    border-gray-300
                                    px-3
                                    py-3
                                "
                            >
                                {{
                                    $massIntention->additional_message
                                    ?: '—'
                                }}
                            </td>


                            <td
                                class="
                                    border
                                    border-gray-300
                                    px-3
                                    py-3
                                "
                            >
                                {{ $massIntention->intention_type }}
                            </td>


                            <td
                                class="
                                    border
                                    border-gray-300
                                    px-3
                                    py-3
                                    text-right
                                    font-semibold
                                "
                            >
                                ₱{{
                                    number_format(
                                        $massIntention->offering_amount,
                                        2
                                    )
                                }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="
                                    border
                                    border-gray-300
                                    px-3
                                    py-8
                                    text-center
                                    text-gray-500
                                "
                            >
                                No approved Mass Intentions found.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


<script>

function sharePrintlist()
{
    if (
        navigator.share
    ) {

        navigator.share({

            title:
                'Loboc Parish Mass Intention List',

            text:
                'Loboc Parish Mass Intention List',

            url:
                window.location.href

        });

    } else {

        navigator.clipboard
            .writeText(
                window.location.href
            )
            .then(function () {

                alert(
                    'Printlist link copied.'
                );

            });

    }
}

</script>


<style>

@media print {

    body * {
        visibility: hidden;
    }


    #printArea,
    #printArea * {
        visibility: visible;
    }


    #printArea {

        position: absolute;

        left: 0;

        top: 0;

        width: 100%;

        border: none;

        padding: 0;

    }


    @page {

        size: A4 portrait;

        margin: 15mm;

    }

}

</style>

@endsection