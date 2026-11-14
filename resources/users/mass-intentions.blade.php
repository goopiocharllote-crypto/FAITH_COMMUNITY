@extends('layouts.user')

@section('title', 'Mass Intentions | Faith Community Platform')

@section('page-title', 'Mass Intentions')

@section('content')

<div class="mb-7">

    <h2 class="text-3xl text-gray-900">
        Request Mass Intention
    </h2>

    <p class="font-bold italic text-gray-600 mt-1">
        Fill out the form below to request a Mass.
    </p>

</div>

@if ($errors->any() && ! $errors->has('payment'))
    <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
        <p class="font-semibold">Please correct the following before submitting:</p>
        <ul class="mt-2 list-inside list-disc space-y-1 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form
    id="mass-intention-form"
    method="POST"
    action="{{ route('mass-intentions.store') }}"
    class="w-full"
>

    @csrf


    <div
        class="
            grid
            grid-cols-1
            lg:grid-cols-2
            gap-8
            w-full
        "
    >


        <!-- ================================================= -->
        <!-- LEFT -->
        <!-- ================================================= -->

        <div class="w-full min-w-0">


            <!-- ================================================= -->
            <!-- INTENTION TYPE -->
            <!-- ================================================= -->

            <fieldset class="mb-6 w-full min-w-0">

                <legend
                    class="
                        block
                        text-xl
                        font-bold
                        mb-3
                        text-gray-900
                    "
                >

                    Intention Type
                    <span class="text-red-500">*</span>

                </legend>


                <div
                    class="
                        grid
                        grid-cols-2
                        sm:grid-cols-3
                        gap-2
                        w-full
                    "
                >


                    <!-- ========================================= -->
                    <!-- THANKSGIVING -->
                    <!-- ========================================= -->

                    <label
                        class="
                            cursor-pointer
                            block
                            w-full
                            min-w-0
                        "
                    >

                        <input
                            type="radio"
                            name="intention_type"
                            value="Thanksgiving"
                            class="hidden intention-radio"
                            required
                            {{ old('intention_type', 'Thanksgiving') === 'Thanksgiving' ? 'checked' : '' }}
                        >


                        <div
                            class="
                                intention-option
                                w-full
                                h-[100px]
                                border
                                border-gray-300
                                rounded-xl
                                p-2
                                text-center
                                flex
                                flex-col
                                items-center
                                justify-center
                                transition
                                duration-200
                            "
                        >

                            <div
                                class="
                                    text-3xl
                                    leading-none
                                "
                            >
                                ✝
                            </div>


                            <div
                                class="
                                    font-semibold
                                    text-[12px]
                                    mt-2
                                    leading-tight
                                    text-center
                                    whitespace-normal
                                    break-words
                                "
                            >
                                Thanksgiving
                            </div>

                        </div>

                    </label>



                    <!-- ========================================= -->
                    <!-- HEALING -->
                    <!-- ========================================= -->

                    <label
                        class="
                            cursor-pointer
                            block
                            w-full
                            min-w-0
                        "
                    >

                        <input
                            type="radio"
                            name="intention_type"
                            value="Healing"
                            class="hidden intention-radio"
                            {{ old('intention_type') === 'Healing' ? 'checked' : '' }}
                        >


                        <div
                            class="
                                intention-option
                                w-full
                                h-[100px]
                                border
                                border-gray-300
                                rounded-xl
                                p-2
                                text-center
                                flex
                                flex-col
                                items-center
                                justify-center
                                transition
                                duration-200
                            "
                        >

                            <div
                                class="
                                    text-3xl
                                    leading-none
                                "
                            >
                                ⚕
                            </div>


                            <div
                                class="
                                    font-semibold
                                    text-[12px]
                                    mt-2
                                    leading-tight
                                    text-center
                                    whitespace-normal
                                    break-words
                                "
                            >
                                Healing
                            </div>

                        </div>

                    </label>



                    <!-- ========================================= -->
                    <!-- BIRTHDAY -->
                    <!-- ========================================= -->

                    <label
                        class="
                            cursor-pointer
                            block
                            w-full
                            min-w-0
                        "
                    >

                        <input
                            type="radio"
                            name="intention_type"
                            value="Birthday"
                            class="hidden intention-radio"
                            {{ old('intention_type') === 'Birthday' ? 'checked' : '' }}
                        >


                        <div
                            class="
                                intention-option
                                w-full
                                h-[100px]
                                border
                                border-gray-300
                                rounded-xl
                                p-2
                                text-center
                                flex
                                flex-col
                                items-center
                                justify-center
                                transition
                                duration-200
                            "
                        >

                            <div
                                class="
                                    text-3xl
                                    leading-none
                                "
                            >
                                🎂
                            </div>


                            <div
                                class="
                                    font-semibold
                                    text-[12px]
                                    mt-2
                                    leading-tight
                                    text-center
                                    whitespace-normal
                                    break-words
                                "
                            >
                                Birthday
                            </div>

                        </div>

                    </label>



                    <!-- ========================================= -->
                    <!-- SOULS -->
                    <!-- ========================================= -->

                    <label
                        class="
                            cursor-pointer
                            block
                            w-full
                            min-w-0
                        "
                    >

                        <input
                            type="radio"
                            name="intention_type"
                            value="Souls"
                            class="hidden intention-radio"
                            {{ old('intention_type') === 'Souls' ? 'checked' : '' }}
                        >


                        <div
                            class="
                                intention-option
                                w-full
                                h-[100px]
                                border
                                border-gray-300
                                rounded-xl
                                p-2
                                text-center
                                flex
                                flex-col
                                items-center
                                justify-center
                                transition
                                duration-200
                            "
                        >

                            <div
                                class="
                                    text-3xl
                                    leading-none
                                "
                            >
                                🕊️
                            </div>


                            <div
                                class="
                                    font-semibold
                                    text-[12px]
                                    mt-2
                                    leading-tight
                                    text-center
                                    whitespace-normal
                                    break-words
                                "
                            >
                                Souls
                            </div>

                        </div>

                    </label>



                    <!-- ========================================= -->
                    <!-- SPECIAL PRAYER -->
                    <!-- ========================================= -->

                    <label
                        class="
                            cursor-pointer
                            block
                            w-full
                            min-w-0
                        "
                    >

                        <input
                            type="radio"
                            name="intention_type"
                            value="Special Prayer"
                            class="hidden intention-radio"
                            {{ old('intention_type') === 'Special Prayer' ? 'checked' : '' }}
                        >


                        <div
                            class="
                                intention-option
                                w-full
                                h-[100px]
                                border
                                border-gray-300
                                rounded-xl
                                p-2
                                text-center
                                flex
                                flex-col
                                items-center
                                justify-center
                                transition
                                duration-200
                            "
                        >

                            <div
                                class="
                                    text-3xl
                                    leading-none
                                "
                            >
                                🙏
                            </div>


                            <div
                                class="
                                    font-semibold
                                    text-[12px]
                                    mt-2
                                    leading-tight
                                    text-center
                                    whitespace-normal
                                    break-words
                                "
                            >
                                Special Prayer
                            </div>

                        </div>

                    </label>

                </div>

            </fieldset>



            <!-- ================================================= -->
            <!-- INTENTION FOR -->
            <!-- ================================================= -->

            <div class="mb-6">

                <label
                    for="intention_for"
                    class="block text-xl font-bold mb-2"
                >

                    Intention for
                    <span class="text-red-500">*</span>

                </label>


                <input
                    id="intention_for"
                    type="text"
                    name="intention_for"
                    value="{{ old('intention_for') }}"
                    required
                    placeholder="Enter name or intention"
                    class="
                        w-full
                        px-4
                        py-3
                        border
                        border-gray-300
                        rounded-xl
                        outline-none
                        focus:border-blue-500
                        focus:ring-2
                        focus:ring-blue-100
                    "
                >

            </div>



            <!-- ================================================= -->
            <!-- MESSAGE -->
            <!-- ================================================= -->

            <div class="mb-6">

                <label
                    for="additional_message"
                    class="block text-xl font-bold mb-2"
                >

                    Additional Message

                    <span class="text-gray-400 font-normal text-base">
                        (Optional)
                    </span>

                </label>


                <textarea
                    id="additional_message"
                    name="additional_message"
                    rows="4"
                    placeholder="Write an additional message..."
                    class="
                        w-full
                        px-4
                        py-3
                        border
                        border-gray-300
                        rounded-xl
                        outline-none
                        resize-none
                        focus:border-blue-500
                        focus:ring-2
                        focus:ring-blue-100
                    "
                >{{ old('additional_message') }}</textarea>

            </div>

        </div>



        <!-- ================================================= -->
        <!-- RIGHT -->
        <!-- ================================================= -->

        <div class="w-full min-w-0">


            <!-- ================================================= -->
            <!-- DATE -->
            <!-- ================================================= -->

            <div class="mb-6">

                <label
                    for="mass_date_date"
                    class="block text-xl font-bold mb-2"
                >

                    Select Date & Time
                    <span class="text-red-500">*</span>

                </label>


                <input
                    id="mass_date"
                    type="hidden"
                    name="mass_date"
                    value="{{ old('mass_date', request('mass_date')) }}"
                >
                        <input
                            id="schedule_id"
                            type="hidden"
                            name="schedule_id"
                            value="{{ old('schedule_id', request('schedule_id')) }}"
                        >

                        <button
                            id="mass-date-trigger"
                            type="button"
                            aria-haspopup="dialog"
                            aria-controls="mass-date-dialog"
                            class="group flex w-full items-center justify-between gap-4 rounded-xl border border-slate-300 bg-white px-4 py-3 text-left shadow-sm transition hover:border-blue-400 hover:shadow focus:outline-none focus:ring-4 focus:ring-blue-100"
                        >
                            <span class="flex min-w-0 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-800" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 3v3m8-3v3M4.5 9h15M5 5.5h14A1.5 1.5 0 0 1 20.5 7v12A1.5 1.5 0 0 1 19 20.5H5A1.5 1.5 0 0 1 3.5 19V7A1.5 1.5 0 0 1 5 5.5Z" />
                                    </svg>
                                </span>
                                <span class="min-w-0">
                                    <span id="mass-date-summary" class="block truncate font-semibold text-slate-900">Choose a scheduled Mass</span>
                                    <span class="mt-0.5 block text-sm text-slate-500">Request availability and closing times are shown for each schedule</span>
                                </span>
                            </span>
                            <span class="shrink-0 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white transition group-hover:bg-blue-800">View schedules</span>
                        </button>
                        <p id="mass-date-error" class="mt-2 hidden text-sm font-medium text-red-700" role="alert"></p>

                        <dialog
                            id="mass-date-dialog"
                            aria-labelledby="mass-date-dialog-title"
                            class="m-auto max-h-[92vh] w-[calc(100%-1.5rem)] max-w-5xl overflow-y-auto rounded-2xl border-0 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/60"
                        >
                            <div class="date-time-picker">
                                <header class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-200 bg-white px-5 py-4 sm:px-7">
                                    <div>
                                        <h2 id="mass-date-dialog-title" class="text-xl font-bold text-slate-900">Choose a Mass schedule</h2>
                                        <p class="mt-1 text-sm text-slate-500">Each schedule shows when intention requests close. Closed schedules cannot be selected.</p>
                                    </div>
                                    <button type="button" class="close-date-picker flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800" aria-label="Close date and time picker">&times;</button>
                                </header>

                                <div class="grid max-h-[65vh] gap-4 overflow-y-auto bg-slate-50 p-4 sm:p-6 md:grid-cols-[minmax(0,1.1fr)_minmax(260px,1fr)]">
                                    <section aria-label="Mass date calendar" class="min-w-0 rounded-xl border border-slate-200 bg-white p-4">
                                        <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                                            <button type="button" class="calendar-previous-month flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-lg text-slate-700 transition hover:bg-slate-50" aria-label="Previous month">&lsaquo;</button>
                                            <p class="calendar-month-label font-semibold text-slate-800" aria-live="polite"></p>
                                            <button type="button" class="calendar-next-month flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-lg text-slate-700 transition hover:bg-slate-50" aria-label="Next month">&rsaquo;</button>
                                        </div>
                                        <div class="grid grid-cols-7 gap-y-1 text-center text-xs font-medium text-slate-500" aria-hidden="true">
                                            <span class="py-2">Su</span><span class="py-2">Mo</span><span class="py-2">Tu</span><span class="py-2">We</span><span class="py-2">Th</span><span class="py-2">Fr</span><span class="py-2">Sa</span>
                                        </div>
                                        <div class="calendar-days grid grid-cols-7 gap-y-1 text-center" role="group" aria-label="Choose a Mass date"></div>
                                        <p class="mt-4 border-t border-slate-100 pt-3 text-xs text-slate-500">Dates with a blue dot have a scheduled Mass. Select a date to view its times and request deadline.</p>
                                    </section>

                                    <section aria-label="Mass times and request closing" class="min-w-0 rounded-xl border border-slate-200 bg-white p-4">
                                        <h3 class="mb-3 border-b border-slate-100 pb-3 font-semibold text-slate-800">Mass time</h3>
                                        <p class="schedule-date-heading mb-3 text-sm text-slate-600" aria-live="polite">Choose a date on the calendar.</p>
                                        <div class="schedule-options space-y-3">
                                    @forelse ($upcomingSchedules as $schedule)
                                        @php
                                            $requestOpen = $schedule->closing_at
                                                && $schedule->closing_at->isFuture()
                                                && $schedule->mass_date->isFuture()
                                                && $schedule->closing_at->lt($schedule->mass_date);
                                            $alreadyRequested = in_array($schedule->id, $requestedScheduleIds ?? []);
                                            $requestOpen = $requestOpen && ! $alreadyRequested;
                                        @endphp
                                        <button
                                            type="button"
                                            class="schedule-option hidden w-full flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 text-left shadow-sm transition sm:flex-row sm:items-center sm:justify-between {{ $requestOpen ? 'hover:border-blue-400 hover:shadow' : 'cursor-not-allowed opacity-70' }}"
                                            data-schedule-id="{{ $schedule->id }}"
                                            data-mass-date="{{ $schedule->mass_date->format('Y-m-d\TH:i:s') }}"
                                            data-mass-day="{{ $schedule->mass_date->format('Y-m-d') }}"
                                            data-schedule-title="{{ $schedule->title }}"
                                            data-display-date="{{ $schedule->mass_date->format('l, F j, Y') }}"
                                            data-display-time="{{ $schedule->mass_date->format('g:i A') }}"
                                            data-closing-at="{{ $schedule->closing_at?->format('Y-m-d\TH:i:s') ?? '' }}"
                                            data-closing-display="{{ $schedule->closing_at?->format('l, F j, Y · g:i A') ?? 'Not set' }}"
                                            data-request-open="{{ $requestOpen ? 'true' : 'false' }}"
                                            data-already-requested="{{ $alreadyRequested ? 'true' : 'false' }}"
                                            @disabled(! $requestOpen)
                                            aria-pressed="false"
                                        >
                                            <span>
                                                <span class="block font-semibold text-slate-900">{{ $schedule->title }}</span>
                                                <span class="mt-1 block text-sm text-slate-600">{{ $schedule->mass_date->format('l, F j, Y · g:i A') }}</span>
                                                <span class="mt-1 block text-sm text-slate-500">Requests close: {{ $schedule->closing_at?->format('M j, Y · g:i A') ?? 'Not set' }}</span>
                                            </span>
                                            <span class="schedule-option-status shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $requestOpen ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $alreadyRequested ? 'Already requested' : ($requestOpen ? 'Open for requests' : 'Requests closed') }}
                                            </span>
                                        </button>
                                    @empty
                                        <p class="schedule-empty rounded-xl border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-600">There are no upcoming Mass schedules available.</p>
                                    @endforelse
                                        <p class="schedule-empty-message rounded-xl border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-600" hidden>No Mass is scheduled for this date. Choose a date with a blue dot.</p>
                                        </div>
                                    </section>
                                </div>

                                <footer class="flex flex-col gap-3 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                                    <p class="selected-date-label min-h-5 text-sm text-slate-600" aria-live="polite">Choose a date and Mass time.</p>
                                    <div class="flex justify-end gap-3">
                                        <button type="button" class="cancel-date-picker rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                                        <button type="button" class="confirm-date-picker rounded-lg bg-blue-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200" disabled>Confirm Mass schedule</button>
                                    </div>
                                </footer>
                            </div>
                        </dialog>

                    </div>
                </div>

            </div>



            <!-- ================================================= -->
            <!-- MOBILE -->
            <!-- ================================================= -->

            <div class="mb-6">

                <label
                    for="mobile"
                    class="block text-xl font-bold mb-2"
                >

                    Mobile Number
                    <span class="text-red-500">*</span>

                </label>


                <div class="flex w-full">

                    <div
                        class="
                            flex
                            items-center
                            px-4
                            border
                            border-r-0
                            border-gray-300
                            rounded-l-xl
                            bg-gray-50
                            flex-shrink-0
                        "
                    >
                        +63
                    </div>


                    <input
                        id="mobile"
                        type="text"
                        name="mobile"
                        value="{{ old('mobile', auth()->user()->mobile) }}"
                        maxlength="10"
                        required
                        inputmode="numeric"
                        pattern="9[0-9]{9}"
                        placeholder="9XXXXXXXXX"
                        class="
                            flex-1
                            min-w-0
                            px-4
                            py-3
                            border
                            border-gray-300
                            rounded-r-xl
                            outline-none
                            focus:border-blue-500
                            focus:ring-2
                            focus:ring-blue-100
                        "
                    >

                </div>

            </div>



            <!-- ================================================= -->
            <!-- OFFERING -->
            <!-- ================================================= -->

            <div class="mb-6">

                <label
                    for="offering_amount"
                    class="block text-xl font-bold mb-2"
                >

                    Offering Amount
                    <span class="text-red-500">*</span>

                </label>


                <div class="flex w-full">

                    <div
                        class="
                            flex
                            items-center
                            px-4
                            border
                            border-r-0
                            border-gray-300
                            rounded-l-xl
                            bg-gray-50
                            font-bold
                            flex-shrink-0
                        "
                    >
                        ₱
                    </div>


                    <input
                        id="offering_amount"
                        type="number"
                        name="offering_amount"
                        value="{{ old('offering_amount') }}"
                        min="0"
                        max="99999999.99"
                        step="0.01"
                        required
                        placeholder="100.00"
                        class="
                            flex-1
                            min-w-0
                            px-4
                            py-3
                            border
                            border-gray-300
                            rounded-r-xl
                            outline-none
                            focus:border-blue-500
                            focus:ring-2
                            focus:ring-blue-100
                        "
                    >

                </div>

            </div>


            <!-- ================================================= -->
            <!-- PAYMENT METHOD -->
            <!-- ================================================= -->

            <fieldset class="mb-6">
                <legend class="block text-xl font-bold mb-2">
                    How will you give your offering?
                    <span class="text-red-500">*</span>
                </legend>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer gap-3 rounded-xl border border-gray-300 p-4 transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 has-[:checked]:ring-2 has-[:checked]:ring-blue-100">
                        <input type="radio" name="payment_method" value="GCash" required class="mt-1 h-4 w-4 accent-blue-700" @checked(old('payment_method', 'GCash') === 'GCash')>
                        <span>
                            <span class="block font-semibold text-gray-900">Pay online · GCash</span>
                            <span class="mt-0.5 block text-sm text-gray-600">After submitting, scan the parish GCash QR code, pay, then upload your GCash receipt.</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer gap-3 rounded-xl border border-gray-300 p-4 transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 has-[:checked]:ring-2 has-[:checked]:ring-blue-100">
                        <input type="radio" name="payment_method" value="Cash" required class="mt-1 h-4 w-4 accent-blue-700" @checked(old('payment_method') === 'Cash')>
                        <span>
                            <span class="block font-semibold text-gray-900">Pay at the parish office</span>
                            <span class="mt-0.5 block text-sm text-gray-600">Bring your offering in person before the Mass.</span>
                        </span>
                    </label>
                </div>

                <p class="mt-3 text-sm text-gray-500">A confirmation is sent to your email and mobile number, and again when the parish reviews your request.</p>
            </fieldset>



            <!-- ================================================= -->
            <!-- SUBMIT -->
            <!-- ================================================= -->

            <button
                type="submit"
                class="
                    w-full
                    bg-blue-600
                    hover:bg-blue-700
                    text-white
                    font-bold
                    text-lg
                    py-4
                    rounded-xl
                    transition
                    duration-200
                "
            >

                Submit Request

            </button>

        </div>

    </div>

</form>



<!-- ===================================================== -->
<!-- LINK TO TRANSACTIONS -->
<!-- ===================================================== -->

<div class="mt-8 flex flex-col gap-3 rounded-xl border border-gray-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h3 class="text-lg font-bold">Already requested a Mass?</h3>
        <p class="mt-1 text-sm text-gray-600">See your reference numbers, status and GCash payments on your own page.</p>
    </div>
    <a href="{{ route('mass-intentions.history') }}" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-blue-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200">
        View my transactions
    </a>
</div>

@push('scripts')

<script>

    const intentionRadios =
        document.querySelectorAll('.intention-radio');


    function updateIntentionTypes() {

        intentionRadios.forEach(function (radio) {

            const option =
                radio.parentElement.querySelector(
                    '.intention-option'
                );


            if (!option) {
                return;
            }


            if (radio.checked) {

                option.classList.add(
                    'border-green-500',
                    'bg-green-50',
                    'ring-2',
                    'ring-green-200'
                );

                option.classList.remove(
                    'border-gray-300'
                );

            } else {

                option.classList.remove(
                    'border-green-500',
                    'bg-green-50',
                    'ring-2',
                    'ring-green-200'
                );

                option.classList.add(
                    'border-gray-300'
                );

            }

        });

    }


    intentionRadios.forEach(function (radio) {

        radio.addEventListener(
            'change',
            updateIntentionTypes
        );

    });


    updateIntentionTypes();

    const massDateForm = document.querySelector('#mass-intention-form');

    if (massDateForm) {
        const massDateValue = massDateForm.querySelector('#mass_date');
        const scheduleIdValue = massDateForm.querySelector('#schedule_id');
        const trigger = massDateForm.querySelector('#mass-date-trigger');
        const summary = massDateForm.querySelector('#mass-date-summary');
        const errorMessage = massDateForm.querySelector('#mass-date-error');
        const dialog = massDateForm.querySelector('#mass-date-dialog');
        const picker = massDateForm.querySelector('.date-time-picker');
        const selectedDateLabel = massDateForm.querySelector('.selected-date-label');
        const confirmButton = picker.querySelector('.confirm-date-picker');
        const scheduleOptions = [...picker.querySelectorAll('.schedule-option')];
        const calendarLabel = picker.querySelector('.calendar-month-label');
        const calendarDays = picker.querySelector('.calendar-days');
        const scheduleDateHeading = picker.querySelector('.schedule-date-heading');
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const availableDates = new Set(scheduleOptions.map((option) => option.dataset.massDay));
        const initiallySelectedSchedule = scheduleOptions.find((option) => option.dataset.scheduleId === scheduleIdValue.value);
        const calendarState = {
            year: initiallySelectedSchedule ? Number(initiallySelectedSchedule.dataset.massDay.slice(0, 4)) : now.getFullYear(),
            month: initiallySelectedSchedule ? Number(initiallySelectedSchedule.dataset.massDay.slice(5, 7)) - 1 : now.getMonth(),
            date: initiallySelectedSchedule?.dataset.massDay || '',
        };
        let draftSchedule = null;

        const setError = (message) => {
            errorMessage.textContent = message;
            errorMessage.classList.toggle('hidden', !message);
            if (message) {
                trigger.setAttribute('aria-describedby', 'mass-date-error');
            } else {
                trigger.removeAttribute('aria-describedby');
            }
        };

        const getOption = (scheduleId) => scheduleOptions.find((option) => option.dataset.scheduleId === String(scheduleId));
        const toDateString = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        const isOpenNow = (option) => {
            const closingAt = option.dataset.closingAt ? new Date(option.dataset.closingAt) : null;
            const massAt = new Date(option.dataset.massDate);
            return option.dataset.requestOpen === 'true'
                && closingAt instanceof Date
                && !Number.isNaN(closingAt.getTime())
                && closingAt > new Date()
                && massAt > new Date()
                && closingAt < massAt;
        };
        const renderSchedulesForDate = () => {
            let visibleCount = 0;
            let selectedStillVisible = false;

            scheduleOptions.forEach((option) => {
                const isVisible = option.dataset.massDay === calendarState.date;
                option.classList.toggle('hidden', !isVisible);
                option.classList.toggle('flex', isVisible);

                if (!isVisible) {
                    return;
                }

                visibleCount++;
                selectedStillVisible ||= option === draftSchedule;

                const requestOpen = isOpenNow(option);
                option.disabled = !requestOpen;
                option.classList.toggle('cursor-not-allowed', !requestOpen);
                option.classList.toggle('opacity-70', !requestOpen);
                const status = option.querySelector('.schedule-option-status');
                status.textContent = option.dataset.alreadyRequested === 'true'
                    ? 'Already requested'
                    : (requestOpen ? 'Open for requests' : 'Requests closed');
                status.classList.toggle('bg-emerald-100', requestOpen);
                status.classList.toggle('text-emerald-800', requestOpen);
                status.classList.toggle('bg-slate-100', !requestOpen);
                status.classList.toggle('text-slate-600', !requestOpen);
            });

            if (!selectedStillVisible) {
                draftSchedule = null;
            }

            scheduleDateHeading.textContent = calendarState.date
                ? new Date(`${calendarState.date}T00:00:00`).toLocaleDateString(undefined, {
                    weekday: 'long',
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric',
                })
                : 'Choose a date on the calendar.';
            const emptyMessage = picker.querySelector('.schedule-empty-message');
            if (emptyMessage) {
                emptyMessage.hidden = visibleCount > 0 || !calendarState.date;
            }

            renderDraftSelection();
        };
        const renderCalendar = () => {
            calendarLabel.textContent = new Date(calendarState.year, calendarState.month, 1)
                .toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
            calendarDays.replaceChildren();

            const firstWeekday = new Date(calendarState.year, calendarState.month, 1).getDay();
            for (let index = 0; index < 42; index++) {
                const date = new Date(calendarState.year, calendarState.month, index - firstWeekday + 1);
                const dateValue = toDateString(date);
                const hasSchedule = availableDates.has(dateValue);
                const isSelected = dateValue === calendarState.date;
                const isCurrentMonth = date.getMonth() === calendarState.month;
                const isPast = date < today;
                const dayButton = document.createElement('button');

                dayButton.type = 'button';
                dayButton.textContent = String(date.getDate());
                dayButton.disabled = isPast || !hasSchedule;
                dayButton.className = `relative mx-auto flex h-10 w-10 items-center justify-center rounded-lg text-sm transition ${isSelected ? 'bg-blue-700 font-semibold text-white shadow-sm' : hasSchedule ? 'font-semibold text-slate-900 hover:bg-blue-50 hover:text-blue-800' : 'text-slate-300'} ${!isCurrentMonth ? 'opacity-50' : ''} ${isPast ? 'cursor-not-allowed' : ''}`;
                dayButton.setAttribute('aria-pressed', String(isSelected));
                dayButton.setAttribute('aria-label', `${date.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}${hasSchedule ? ', Mass scheduled' : ', no Mass scheduled'}`);

                if (hasSchedule && !isSelected) {
                    const marker = document.createElement('span');
                    marker.className = 'absolute bottom-1 h-1 w-1 rounded-full bg-blue-600';
                    marker.setAttribute('aria-hidden', 'true');
                    dayButton.append(marker);
                }

                if (hasSchedule && !isPast) {
                    dayButton.addEventListener('click', () => {
                        calendarState.date = dateValue;
                        calendarState.year = date.getFullYear();
                        calendarState.month = date.getMonth();
                        draftSchedule = null;
                        renderCalendar();
                        renderSchedulesForDate();
                    });
                }

                calendarDays.append(dayButton);
            }
        };

        const renderCommittedValue = () => {
            const selected = getOption(scheduleIdValue.value);

            if (!selected || !isOpenNow(selected)) {
                scheduleIdValue.value = '';
                massDateValue.value = '';
                summary.textContent = 'Choose a scheduled Mass';
                return;
            }

            massDateValue.value = selected.dataset.massDate;
            summary.textContent = `${selected.dataset.scheduleTitle} · ${selected.dataset.displayDate} · ${selected.dataset.displayTime}`;
        };
        const renderDraftSelection = () => {
            scheduleOptions.forEach((option) => {
                const isSelected = option === draftSchedule;
                option.setAttribute('aria-pressed', String(isSelected));
                option.classList.toggle('border-blue-600', isSelected);
                option.classList.toggle('ring-2', isSelected);
                option.classList.toggle('ring-blue-100', isSelected);
            });

            const hasDraft = Boolean(draftSchedule);
            confirmButton.disabled = !hasDraft || !isOpenNow(draftSchedule);
            confirmButton.classList.toggle('cursor-not-allowed', confirmButton.disabled);
            confirmButton.classList.toggle('opacity-50', confirmButton.disabled);
            selectedDateLabel.textContent = hasDraft
                ? `${draftSchedule.dataset.scheduleTitle} · ${draftSchedule.dataset.displayDate} at ${draftSchedule.dataset.displayTime}. Requests close ${draftSchedule.dataset.closingDisplay}.`
                : 'Choose a date and Mass time.';
        };
        const openPicker = () => {
            const selected = getOption(scheduleIdValue.value);
            draftSchedule = selected && isOpenNow(selected) ? selected : null;
            if (selected) {
                calendarState.year = Number(selected.dataset.massDay.slice(0, 4));
                calendarState.month = Number(selected.dataset.massDay.slice(5, 7)) - 1;
                calendarState.date = selected.dataset.massDay;
            }
            setError('');
            renderCalendar();
            renderSchedulesForDate();
            dialog.showModal();
        };

        trigger.addEventListener('click', openPicker);
        picker.querySelector('.calendar-previous-month').addEventListener('click', () => {
            calendarState.month--;
            if (calendarState.month < 0) {
                calendarState.month = 11;
                calendarState.year--;
            }
            renderCalendar();
        });
        picker.querySelector('.calendar-next-month').addEventListener('click', () => {
            calendarState.month++;
            if (calendarState.month > 11) {
                calendarState.month = 0;
                calendarState.year++;
            }
            renderCalendar();
        });
        scheduleOptions.forEach((option) => {
            option.addEventListener('click', () => {
                if (!isOpenNow(option)) {
                    return;
                }
                draftSchedule = option;
                renderDraftSelection();
            });
        });

        confirmButton.addEventListener('click', () => {
            if (!draftSchedule || !isOpenNow(draftSchedule)) {
                setError('Requests for this Mass have closed. Please select another available time.');
                return;
            }

            scheduleIdValue.value = draftSchedule.dataset.scheduleId;
            massDateValue.value = draftSchedule.dataset.massDate;
            renderCommittedValue();
            setError('');
            dialog.close('confirm');
        });
        picker.querySelectorAll('.cancel-date-picker, .close-date-picker').forEach((button) => {
            button.addEventListener('click', () => dialog.close('cancel'));
        });
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close('cancel');
            }
        });
        dialog.addEventListener('close', () => trigger.focus());
        massDateForm.addEventListener('submit', (event) => {
            const selected = getOption(scheduleIdValue.value);
            if (!selected || !isOpenNow(selected)) {
                event.preventDefault();
                setError('Please choose an upcoming Mass with requests still open.');
                openPicker();
            }
        });

        renderCommittedValue();
        if (calendarState.date) {
            renderCalendar();
            renderSchedulesForDate();
        } else {
            scheduleDateHeading.textContent = 'Choose a date on the calendar.';
        }
    }

</script>

@endpush

@endsection