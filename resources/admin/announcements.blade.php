@extends('layouts.admin')

@section('title', 'Announcements | Admin')

@section('page-title', 'Announcements')
@section('page-description', 'Create, edit, and delete parish announcements')

@section('content')


<!-- ADD ANNOUNCEMENT -->

<section
    class="
        bg-white
        border
        border-gray-200
        rounded-xl
        p-6
        mb-8
    "
>

    <h3 class="text-xl font-bold mb-5">
        Add Announcement
    </h3>


    <form
        method="POST"
        action="{{ route('admin.announcements.store') }}"
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
                    Title
                </label>

                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    maxlength="255"
                    placeholder="Enter announcement title"
                    class="
                        w-full
                        px-4
                        py-3
                        border
                        border-gray-300
                        rounded-lg
                        outline-none
                        focus:border-blue-600
                    "
                >

            </div>


            <!-- CONTENT -->

            <div class="md:col-span-2">

                <label
                    for="content"
                    class="
                        block
                        font-semibold
                        text-gray-800
                        mb-2
                    "
                >
                    Announcement
                </label>

                <textarea
                    id="content"
                    name="content"
                    rows="5"
                    required
                    maxlength="10000"
                    placeholder="Write your announcement..."
                    class="
                        w-full
                        px-4
                        py-3
                        border
                        border-gray-300
                        rounded-lg
                        outline-none
                        resize-none
                        focus:border-blue-600
                    "
                >{{ old('content') }}</textarea>

            </div>


            <!-- DATE -->

            <div>

                <label
                    for="announcement_date"
                    class="
                        block
                        font-semibold
                        text-gray-800
                        mb-2
                    "
                >
                    Date
                </label>

                <input
                    id="announcement_date"
                    type="date"
                    name="announcement_date"
                    value="{{ old('announcement_date') }}"
                    class="
                        w-full
                        px-4
                        py-3
                        border
                        border-gray-300
                        rounded-lg
                        outline-none
                        focus:border-blue-600
                    "
                >

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

                    <span class="font-semibold">
                        Show on user dashboard
                    </span>

                </label>

            </div>

        </div>


        <button
            type="submit"
            class="
                mt-5
                px-6
                py-3
                rounded-lg
                bg-blue-600
                hover:bg-blue-700
                text-white
                font-bold
            "
        >
            Add Announcement
        </button>

    </form>

</section>


<!-- EXISTING ANNOUNCEMENTS -->

<section
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
            Manage Announcements
        </h3>

    </div>


    <div>

        @forelse ($announcements as $announcement)

            <div
                class="
                    p-6
                    border-b
                    border-gray-200
                "
            >


                <!-- EDIT -->

                <form
                    method="POST"
                    action="{{
                        route(
                            'admin.announcements.update',
                            $announcement
                        )
                    }}"
                >

                    @csrf

                    @method('PATCH')


                    <div
                        class="
                            grid
                            grid-cols-1
                            md:grid-cols-2
                            gap-4
                        "
                    >


                        <!-- TITLE -->

                        <div class="md:col-span-2">

                            <label
                                class="
                                    block
                                    text-sm
                                    font-semibold
                                    mb-1
                                "
                            >
                                Title
                            </label>

                            <input
                                type="text"
                                name="title"
                                value="{{ old('title', $announcement->title) }}"
                                required
                                maxlength="255"
                                class="
                                    w-full
                                    px-4
                                    py-2.5
                                    border
                                    border-gray-300
                                    rounded-lg
                                "
                            >

                        </div>


                        <!-- CONTENT -->

                        <div class="md:col-span-2">

                            <label
                                class="
                                    block
                                    text-sm
                                    font-semibold
                                    mb-1
                                "
                            >
                                Content
                            </label>

                            <textarea
                                name="content"
                                rows="4"
                                required
                                maxlength="10000"
                                class="
                                    w-full
                                    px-4
                                    py-2.5
                                    border
                                    border-gray-300
                                    rounded-lg
                                    resize-none
                                "
                            >{{ old('content', $announcement->content) }}</textarea>

                        </div>


                        <!-- DATE -->

                        <div>

                            <label
                                class="
                                    block
                                    text-sm
                                    font-semibold
                                    mb-1
                                "
                            >
                                Date
                            </label>

                            <input
                                type="date"
                                name="announcement_date"
                                value="{{ old('announcement_date', $announcement->announcement_date?->format('Y-m-d')) }}"
                                class="
                                    w-full
                                    px-4
                                    py-2.5
                                    border
                                    border-gray-300
                                    rounded-lg
                                "
                            >

                        </div>


                        <!-- ACTIVE -->

                        <div class="flex items-center">

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
                                    @checked(old('is_active', $announcement->is_active))
                                    class="w-5 h-5"
                                >

                                <span>
                                    Show on user dashboard
                                </span>

                            </label>

                        </div>

                    </div>


                    <div class="flex gap-3 mt-5">

                        <button
                            type="submit"
                            class="
                                px-5
                                py-2.5
                                rounded-lg
                                bg-blue-600
                                text-white
                                font-semibold
                                hover:bg-blue-700
                            "
                        >
                            Save Changes
                        </button>

                    </div>

                </form>


                <!-- DELETE -->

                <form
                    method="POST"
                    action="{{
                        route(
                            'admin.announcements.destroy',
                            $announcement
                        )
                    }}"
                    class="mt-3"
                    onsubmit="return confirm(
                        'Are you sure you want to delete this announcement?'
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
                            text-white
                            font-semibold
                            hover:bg-red-700
                        "
                    >
                        Delete Announcement
                    </button>

                </form>

            </div>

        @empty

            <div
                class="
                    py-12
                    text-center
                    text-gray-500
                "
            >
                No announcements have been created yet.
            </div>

        @endforelse

    </div>

</section>

@endsection