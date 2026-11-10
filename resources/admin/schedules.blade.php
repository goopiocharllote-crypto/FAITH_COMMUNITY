@extends('layouts.admin')

@section('title', 'Manage Schedules | Admin')

@section('header-content')

    <h1 class="text-4xl text-gray-900">
        Manage Schedules
    </h1>

@endsection


@section('content')


<div class="mb-8">

    <p class="text-gray-600">
        Create and manage upcoming Mass schedules.
    </p>

</div>


<!-- ===================================================== -->
<!-- ADD SCHEDULE -->
<!-- ===================================================== -->

<section
    class="
        bg-[#fffafa]
        border
        border-[#dfd0d0]
        rounded-2xl
        p-6
        mb-8
    "
>

    <h2 class="text-2xl font-bold text-gray-900 mb-6">
        Add Mass Schedule
    </h2>


    <form
        method="POST"
        action="{{ route('admin.schedules.store') }}"
    >

        @csrf


        <div
            class="
                grid
                grid-cols-1
                md:grid-cols-2
                gap-5
            "
        >


            <!-- TITLE -->

            <div class="md:col-span-2">

                <label
                    for="title"
                    class="
                        block
                        font-semibold
                        text-gray-800
                        mb-2
                    "
                >
                    Mass Title
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    placeholder="Example: Sunday Mass"
                    class="
                        w-full
                        px-4
                        py-3
                        border
                        border-gray-300
                        rounded-xl
                        outline-none
                        focus:border-blue-600
                    "
                >

            </div>


            <!-- MASS DATE AND STANDARD TIME -->

            <div class="md:col-span-2">

                <label
                    for="mass_date_date"
                    class="
                        block
                        font-semibold
                        text-gray-800
                        mb-2
                    "
                >
                    Mass Date and Time
                </label>

                <input
                    type="hidden"
                    name="mass_date"
                    value="{{ old('mass_date') }}"
                    class="mass-date-value"
                >

                <div class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-3 sm:grid-cols-4">
                    <input
                        type="date"
                        id="mass_date_date"
                        required
                        min="{{ now()->format('Y-m-d') }}"
                        aria-label="Mass date"
                        class="mass-date w-full rounded-xl border border-gray-300 px-4 py-3"
                    >
                    <select required aria-label="Mass hour" class="mass-hour w-full rounded-xl border border-gray-300 px-4 py-3">
                        <option value="">Hour</option>
                        @for ($hour = 1; $hour <= 12; $hour++)
                            <option value="{{ sprintf('%02d', $hour) }}">{{ $hour }}</option>
                        @endfor
                    </select>
                    <select required aria-label="Mass minute" class="mass-minute w-full rounded-xl border border-gray-300 px-4 py-3">
                        <option value="">Minute</option>
                        @for ($minute = 0; $minute < 60; $minute++)
                            <option value="{{ sprintf('%02d', $minute) }}">{{ sprintf('%02d', $minute) }}</option>
                        @endfor
                    </select>
                    <select required aria-label="AM or PM" class="mass-period w-full rounded-xl border border-gray-300 px-4 py-3">
                        <option value="">AM/PM</option>
                        <option value="AM">AM</option>
                        <option value="PM">PM</option>
                    </select>
                </div>
                <p class="mt-2 text-sm text-gray-500">Choose the date, then enter the time using the 12-hour clock.</p>

            </div>

            <!-- INTENTION CLOSING TIME -->

            <div class="md:col-span-2">

                <label
                    for="closing_at"
                    class="
                        block
                        font-semibold
                        text-gray-800
                        mb-2
                    "
                >
                    Intention Request Closing Date and Time
                </label>

                <input
                    type="hidden"
                    name="closing_at"
                    value="{{ old('closing_at') }}"
                    class="closing-date-value"
                >

                <div class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-3 sm:grid-cols-4">
                    <input
                        type="date"
                        id="closing_at_date"
                        required
                        min="{{ now()->format('Y-m-d') }}"
                        aria-label="Intention request closing date"
                        class="closing-date w-full rounded-xl border border-gray-300 px-4 py-3"
                    >
                    <select required aria-label="Closing hour" class="closing-hour w-full rounded-xl border border-gray-300 px-4 py-3">
                        <option value="">Hour</option>
                        @for ($hour = 1; $hour <= 12; $hour++)
                            <option value="{{ sprintf('%02d', $hour) }}">{{ $hour }}</option>
                        @endfor
                    </select>
                    <select required aria-label="Closing minute" class="closing-minute w-full rounded-xl border border-gray-300 px-4 py-3">
                        <option value="">Minute</option>
                        @for ($minute = 0; $minute < 60; $minute++)
                            <option value="{{ sprintf('%02d', $minute) }}">{{ sprintf('%02d', $minute) }}</option>
                        @endfor
                    </select>
                    <select required aria-label="Closing AM or PM" class="closing-period w-full rounded-xl border border-gray-300 px-4 py-3">
                        <option value="">AM/PM</option>
                        <option value="AM">AM</option>
                        <option value="PM">PM</option>
                    </select>
                </div>
                <p class="mt-2 text-sm text-gray-500">This deadline is shown to parishioners. Requests close at this time and remain unavailable until the Mass has started. The closing date and time must be before the Mass date and time.</p>

            </div>


            <!-- LIVESTREAM -->

            <div class="md:col-span-2">
                <label for="livestream_url" class="block font-semibold text-gray-800 mb-2">
                    Livestream link <span class="font-normal text-gray-500">(optional)</span>
                </label>
                <input
                    id="livestream_url"
                    type="url"
                    name="livestream_url"
                    value="{{ old('livestream_url') }}"
                    maxlength="500"
                    placeholder="https://www.facebook.com/…/videos/… or https://youtube.com/live/…"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3"
                >
                <p class="mt-2 text-sm text-gray-500">Facebook and YouTube video links play inside the Live Mass page; other links open in a new tab. You can add it later once the stream is scheduled.</p>
                @error('livestream_url')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>


            <!-- ACTIVE -->

            <div class="flex items-center">

                <label
                    class="
                        flex
                        items-center
                        gap-3
                        cursor-pointer
                    "
                >

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', true))
                        class="w-5 h-5"
                    >

                    <span class="font-semibold text-gray-800">
                        Show this schedule
                    </span>

                </label>

            </div>

        </div>


        <button
            type="submit"
            class="
                mt-6
                px-6
                py-3
                bg-blue-600
                hover:bg-blue-700
                text-white
                font-bold
                rounded-xl
            "
        >
            Add Schedule
        </button>

    </form>

</section>



<!-- ===================================================== -->
<!-- EXISTING SCHEDULES -->
<!-- ===================================================== -->

<section
    class="
        bg-[#fffafa]
        border
        border-[#dfd0d0]
        rounded-2xl
        overflow-hidden
    "
>

    <div
        class="
            px-6
            py-5
            border-b
            border-[#dfd0d0]
        "
    >

        <h2 class="text-2xl font-bold text-gray-900">
            Existing Schedules
        </h2>

    </div>


    <div class="md:col-span-2">

        @forelse ($schedules as $schedule)

            <div
                class="
                    p-6
                    border-b
                    border-[#dfd0d0]
                "
            >

                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-medium text-gray-600">
                        {{ $schedule->mass_date->isFuture() ? 'Upcoming schedule' : 'Past schedule' }}
                    </p>
                    @if ($schedule->is_active)
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Visible to parishioners</span>
                    @else
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Hidden from parishioners</span>
                    @endif
                </div>
                <p class="mb-4 text-sm text-gray-600">
                    Intention requests close:
                    <span class="font-semibold text-gray-800">
                        {{ $schedule->closing_at?->format('l, F j, Y · g:i A') ?? 'Not set — requests are unavailable' }}
                    </span>
                </p>
                <p class="mb-4 text-sm text-gray-600">
                    Livestream:
                    @if ($schedule->livestream_url)
                        <a href="{{ $schedule->livestream_url }}" target="_blank" rel="noopener noreferrer" class="break-all font-semibold text-blue-700 hover:underline">{{ $schedule->livestream_url }}</a>
                    @else
                        <span class="font-semibold text-gray-800">No link yet</span>
                    @endif
                </p>

                <!-- UPDATE FORM -->

                <form
                    method="POST"
                    action="{{ route(
                        'admin.schedules.update',
                        $schedule
                    ) }}"
                >

                    @csrf

                    @method('PATCH')


                    <div
                        class="
                            grid
                            grid-cols-1
                            md:grid-cols-2
                            gap-5
                        "
                    >


                        <!-- TITLE -->

                        <div class="md:col-span-2">

                            <label
                                class="
                                    block
                                    font-semibold
                                    text-gray-800
                                    mb-2
                                "
                            >
                                Mass Title
                            </label>

                            <input
                                type="text"
                                name="title"
                                value="{{ $schedule->title }}"
                                required
                                class="
                                    w-full
                                    px-4
                                    py-3
                                    border
                                    border-gray-300
                                    rounded-xl
                                "
                            >

                        </div>


                        <!-- MASS DATE AND STANDARD TIME -->

                        <div class="md:col-span-2">

                            <label
                                for="mass_date_date_{{ $schedule->id }}"
                                class="
                                    block
                                    font-semibold
                                    text-gray-800
                                    mb-2
                                "
                            >
                                Mass Date and Time
                            </label>

                            <input
                                type="hidden"
                                name="mass_date"
                                value="{{ $schedule->mass_date->format('Y-m-d\TH:i') }}"
                                class="mass-date-value"
                            >

                            <div class="mt-2 grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-3 sm:grid-cols-4">
                                <input
                                    type="date"
                                    id="mass_date_date_{{ $schedule->id }}"
                                    value="{{ $schedule->mass_date->format('Y-m-d') }}"
                                    required
                                    min="{{ now()->format('Y-m-d') }}"
                                    aria-label="Mass date"
                                    class="mass-date w-full rounded-xl border border-gray-300 px-4 py-3"
                                >
                                <select required aria-label="Mass hour" class="mass-hour w-full rounded-xl border border-gray-300 px-4 py-3">
                                    <option value="">Hour</option>
                                    @for ($hour = 1; $hour <= 12; $hour++)
                                        <option value="{{ sprintf('%02d', $hour) }}" @selected($schedule->mass_date->format('h') === sprintf('%02d', $hour))>{{ $hour }}</option>
                                    @endfor
                                </select>
                                <select required aria-label="Mass minute" class="mass-minute w-full rounded-xl border border-gray-300 px-4 py-3">
                                    <option value="">Minute</option>
                                    @for ($minute = 0; $minute < 60; $minute++)
                                        <option value="{{ sprintf('%02d', $minute) }}" @selected($schedule->mass_date->format('i') === sprintf('%02d', $minute))>{{ sprintf('%02d', $minute) }}</option>
                                    @endfor
                                </select>
                                <select required aria-label="AM or PM" class="mass-period w-full rounded-xl border border-gray-300 px-4 py-3">
                                    <option value="AM" @selected($schedule->mass_date->format('A') === 'AM')>AM</option>
                                    <option value="PM" @selected($schedule->mass_date->format('A') === 'PM')>PM</option>
                                </select>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Use the 12-hour clock. The closing date and time must be before the Mass date and time.</p>

                        </div>


                        <!-- INTENTION CLOSING TIME -->

                        <div>

                            <label
                                for="closing_at_{{ $schedule->id }}"
                                class="
                                    block
                                    font-semibold
                                    text-gray-800
                                    mb-2
                                "
                            >
                                Intention Request Closing Date and Time
                            </label>

                            <input
                                type="hidden"
                                name="closing_at"
                                value="{{ old('closing_at', $schedule->closing_at?->format('Y-m-d\TH:i')) }}"
                                class="closing-date-value"
                            >

                            <div class="mt-2 grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-3 sm:grid-cols-4">
                                <input
                                    type="date"
                                    id="closing_at_date_{{ $schedule->id }}"
                                    value="{{ $schedule->closing_at?->format('Y-m-d') }}"
                                    required
                                    min="{{ now()->format('Y-m-d') }}"
                                    aria-label="Intention request closing date"
                                    class="closing-date w-full rounded-xl border border-gray-300 px-4 py-3"
                                >
                                <select required aria-label="Closing hour" class="closing-hour w-full rounded-xl border border-gray-300 px-4 py-3">
                                    <option value="">Hour</option>
                                    @for ($hour = 1; $hour <= 12; $hour++)
                                        <option value="{{ sprintf('%02d', $hour) }}" @selected($schedule->closing_at?->format('h') === sprintf('%02d', $hour))>{{ $hour }}</option>
                                    @endfor
                                </select>
                                <select required aria-label="Closing minute" class="closing-minute w-full rounded-xl border border-gray-300 px-4 py-3">
                                    <option value="">Minute</option>
                                    @for ($minute = 0; $minute < 60; $minute++)
                                        <option value="{{ sprintf('%02d', $minute) }}" @selected($schedule->closing_at?->format('i') === sprintf('%02d', $minute))>{{ sprintf('%02d', $minute) }}</option>
                                    @endfor
                                </select>
                                <select required aria-label="Closing AM or PM" class="closing-period w-full rounded-xl border border-gray-300 px-4 py-3">
                                    <option value="AM" @selected($schedule->closing_at?->format('A') === 'AM')>AM</option>
                                    <option value="PM" @selected($schedule->closing_at?->format('A') === 'PM')>PM</option>
                                </select>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">12-hour clock</p>

                        </div>


                        <!-- LIVESTREAM -->

                        <div class="md:col-span-2">
                            <label for="livestream_url_{{ $schedule->id }}" class="block font-semibold mb-2">
                                Livestream link <span class="font-normal text-gray-500">(optional)</span>
                            </label>
                            <input
                                id="livestream_url_{{ $schedule->id }}"
                                type="url"
                                name="livestream_url"
                                value="{{ $schedule->livestream_url }}"
                                maxlength="500"
                                placeholder="https://www.facebook.com/…/videos/…"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3"
                            >
                        </div>


                        <!-- ACTIVE -->

                        <div>

                            <label
                                class="
                                    flex
                                    items-center
                                    gap-3
                                "
                            >

                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    @checked($schedule->is_active)
                                    class="w-5 h-5"
                                >

                                <span class="font-semibold">
                                    Show this schedule
                                </span>

                            </label>

                        </div>

                    </div>


                    <div class="mt-5">

                        <button
                            type="submit"
                            class="
                                px-5
                                py-2.5
                                rounded-lg
                                bg-blue-600
                                hover:bg-blue-700
                                text-white
                                font-semibold
                            "
                        >
                            Save Changes
                        </button>

                    </div>

                </form>


                <!-- DELETE FORM -->

                <form
                    method="POST"
                    action="{{ route(
                        'admin.schedules.destroy',
                        $schedule
                    ) }}"
                    class="mt-3"
                    onsubmit="return confirm(
                        'Are you sure you want to delete this Mass schedule?'
                    )"
                >

                    @csrf

                    @method('DELETE')

                    <button
                        type="submit"
                        class="
                            px-5
                            py-2.5
                            rounded-lg
                            bg-red-600
                            hover:bg-red-700
                            text-white
                            font-semibold
                        "
                    >
                        Delete Schedule
                    </button>

                </form>

            </div>

        @empty

            <div
                class="
                    py-14
                    text-center
                    text-gray-500
                "
            >

                <div class="text-4xl mb-3">
                    ▦
                </div>

                <p class="font-semibold">
                    No Mass schedules yet.
                </p>

                <p class="text-sm mt-1">
                    Add a schedule using the form above.
                </p>

            </div>

        @endforelse

    </div>

</section>

<script>
    document.querySelectorAll('form').forEach((form) => {
        [
            {
                hidden: '.mass-date-value',
                date: '.mass-date',
                hour: '.mass-hour',
                minute: '.mass-minute',
                period: '.mass-period',
            },
            {
                hidden: '.closing-date-value',
                date: '.closing-date',
                hour: '.closing-hour',
                minute: '.closing-minute',
                period: '.closing-period',
            },
        ].forEach((selectors) => {
            const hiddenDate = form.querySelector(selectors.hidden);
            const dateInput = form.querySelector(selectors.date);
            const hourInput = form.querySelector(selectors.hour);
            const minuteInput = form.querySelector(selectors.minute);
            const periodInput = form.querySelector(selectors.period);

            if (!hiddenDate || !dateInput || !hourInput || !minuteInput || !periodInput) {
                return;
            }

            if (hiddenDate.value) {
                const match = hiddenDate.value.match(/^(\d{4}-\d{2}-\d{2})T(\d{2}):(\d{2})$/);

                if (match && !dateInput.value) {
                    const hour24 = Number(match[2]);
                    dateInput.value = match[1];
                    hourInput.value = String(hour24 % 12 || 12).padStart(2, '0');
                    minuteInput.value = match[3];
                    periodInput.value = hour24 >= 12 ? 'PM' : 'AM';
                }
            }

            const updateHiddenDate = () => {
                if (!dateInput.value || !hourInput.value || !minuteInput.value || !periodInput.value) {
                    hiddenDate.value = '';
                    return;
                }

                const hour12 = Number(hourInput.value);
                const hour24 = (hour12 % 12) + (periodInput.value === 'PM' ? 12 : 0);
                hiddenDate.value = `${dateInput.value}T${String(hour24).padStart(2, '0')}:${minuteInput.value}`;
            };

            [dateInput, hourInput, minuteInput, periodInput].forEach((input) => {
                input.addEventListener('change', updateHiddenDate);
            });

            form.addEventListener('submit', updateHiddenDate);
        });
    });
</script>

@endsection