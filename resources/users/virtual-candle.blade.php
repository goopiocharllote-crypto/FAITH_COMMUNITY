<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Virtual Candle | Faith Community Platform
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>

        /* ===================================================== */
        /* GLOBAL */
        /* ===================================================== */

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            font-family: Georgia, 'Times New Roman', serif;
            background: #ffffff;
        }


        /* ===================================================== */
        /* CANDLE AREA */
        /* ===================================================== */

        .candle-area {
            position: relative;
            min-height: 100vh;
            overflow: hidden;

            background:
                radial-gradient(
                    circle at 50% 42%,
                    rgba(255, 181, 35, 0.82) 0%,
                    rgba(143, 65, 5, 0.88) 30%,
                    rgba(45, 18, 2, 0.96) 65%,
                    #050201 100%
                );
        }


        /* ===================================================== */
        /* CANDLE GLOW */
        /* ===================================================== */

        .candle-glow {
            position: absolute;

            left: 50%;
            top: 43%;

            transform: translate(-50%, -50%);

            width: 360px;
            height: 360px;

            border-radius: 50%;

            background:
                radial-gradient(
                    circle,
                    rgba(255, 230, 145, 0.42) 0%,
                    rgba(255, 159, 15, 0.22) 35%,
                    rgba(255, 94, 0, 0.07) 62%,
                    transparent 76%
                );

            filter: blur(18px);

            pointer-events: none;
        }


        /* ===================================================== */
        /* CANDLE */
        /* ===================================================== */

        .candle {
            position: absolute;

            left: 50%;
            bottom: 70px;

            transform: translateX(-50%);

            width: 140px;
            height: 250px;

            border-radius:
                32px
                32px
                22px
                22px;

            background:
                linear-gradient(
                    90deg,
                    #8e3900 0%,
                    #cf6908 18%,
                    #f5bd3d 42%,
                    #ffe47b 55%,
                    #f7d35d 72%,
                    #a94400 100%
                );

            box-shadow:
                inset -16px 0 25px rgba(120, 42, 0, 0.40),
                inset 13px 0 22px rgba(255, 245, 180, 0.25),
                0 28px 40px rgba(0, 0, 0, 0.55);

            z-index: 3;
        }


        /* ===================================================== */
        /* CANDLE TOP */
        /* ===================================================== */

        .candle::before {
            content: "";

            position: absolute;

            top: -16px;
            left: 0;

            width: 140px;
            height: 30px;

            border-radius: 50%;

            background:
                radial-gradient(
                    ellipse,
                    #f8d36a 0%,
                    #ffd98a 48%,
                    #b85a07 100%
                );

            box-shadow:
                inset 0 -7px 10px rgba(130, 52, 0, 0.35);
        }


        /* ===================================================== */
        /* WAX CENTER */
        /* ===================================================== */

        .candle-wax {
            position: absolute;

            top: 3px;
            left: 13px;

            width: 114px;
            height: 21px;

            border-radius: 50%;

            background:
                radial-gradient(
                    ellipse,
                    #fff5bd 0%,
                    #f8d45d 65%,
                    #d57a11 100%
                );

            z-index: 1;
        }


        /* ===================================================== */
        /* WICK */
        /* ===================================================== */

        .wick {
            position: absolute;

            left: 50%;
            top: -45px;

            transform: translateX(-50%);

            width: 5px;
            height: 60px;

            background: #2b1608;

            border-radius: 6px;

            z-index: 5;
        }


        /* ===================================================== */
        /* FLAME */
        /* ===================================================== */

        .flame {
            position: absolute;

            left: 50%;
            top: -125px;

            transform:
                translateX(-50%)
                scale(1);

            width: 48px;
            height: 90px;

            border-radius:
                55%
                55%
                50%
                50%;

            background:
                radial-gradient(
                    ellipse at 50% 70%,
                    #fffde8 0%,
                    #fff7a0 22%,
                    #ffcf37 47%,
                    #ff8f00 73%,
                    transparent 76%
                );

            filter: blur(0.6px);

            box-shadow:
                0 0 35px rgba(255, 184, 0, 0.80),
                0 0 75px rgba(255, 130, 0, 0.48);

            animation:
                flicker 1s infinite alternate;

            transform-origin: center bottom;

            z-index: 6;
        }

        .flame.hidden-flame {
            display: none;
        }


        /* ===================================================== */
        /* FLAME ANIMATION */
        /* ===================================================== */

        @keyframes flicker {

            0% {
                transform:
                    translateX(-50%)
                    scale(1)
                    rotate(-2deg);
            }

            50% {
                transform:
                    translateX(-50%)
                    scale(1.05, 0.96)
                    rotate(2deg);
            }

            100% {
                transform:
                    translateX(-50%)
                    scale(0.96, 1.05)
                    rotate(-1deg);
            }

        }


        /* ===================================================== */
        /* RIGHT PANEL */
        /* ===================================================== */

        .input-box {
            background: #fffafa;
            border: 1px solid #e2caca;
            color: #222222;
        }

        .input-box:focus {
            border-color: #93c5fd;
        }

        .prayer-text {
            min-height: 124px;
        }

        .outline-button {
            border: 1px solid #e2caca;
            background: #fffafa;
            color: #075985;
        }

        .outline-button:hover {
            background: #fff4f4;
        }


        /* ===================================================== */
        /* RESPONSIVE CANDLE */
        /* ===================================================== */

        @media (max-width: 1024px) {

            .candle-area {
                min-height: 500px;
            }

            .candle {
                width: 175px;
                height: 310px;
                bottom: 75px;
            }

            .candle::before {
                width: 175px;
            }

            .candle-wax {
                width: 143px;
            }

            .candle-glow {
                width: 400px;
                height: 400px;
            }

        }


        @media (max-width: 640px) {

            .candle-area {
                min-height: 500px;
            }

            .candle {
                width: 150px;
                height: 270px;
                bottom: 65px;
                border-radius:
                    26px
                    26px
                    18px
                    18px;
            }

            .candle::before {
                width: 150px;
                height: 32px;
            }

            .candle-wax {
                width: 124px;
                height: 22px;
                left: 13px;
            }

            .wick {
                height: 58px;
                top: -45px;
            }

            .flame {
                width: 52px;
                height: 98px;
                top: -130px;
            }

            .candle-glow {
                width: 340px;
                height: 340px;
            }

        }

    </style>

</head>


<body class="min-h-screen bg-white">


<div class="flex min-h-screen flex-col md:flex-row">
    {!! view()->file(resource_path('users/navbar.blade.php'))->render() !!}

    <!-- ===================================================== -->
    <!-- MAIN -->
    <!-- ===================================================== -->

    <main class="flex-1 min-h-screen">


        <div
            class="
                grid
                grid-cols-1
                lg:grid-cols-2
                min-h-screen
            "
        >


            <!-- ================================================= -->
            <!-- CANDLE SECTION -->
            <!-- ================================================= -->

            <section
                class="
                    candle-area
                    flex
                    items-center
                    justify-center
                "
            >

                <div
                    class="candle-glow"
                    aria-hidden="true"
                ></div>


                <!-- IMPORTANT:
                     No "relative" class here.
                     The CSS .candle must remain absolute.
                -->

                <div
                    class="candle"
                    id="candle"
                >

                    <div
                        class="candle-wax"
                        aria-hidden="true"
                    ></div>


                    <div
                        class="flame"
                        id="flame"
                    ></div>


                    <div
                        class="wick"
                        aria-hidden="true"
                    ></div>

                </div>

            </section>



            <!-- ================================================= -->
            <!-- CONTROL SECTION -->
            <!-- ================================================= -->

            <section
                class="
                    bg-white
                    px-6
                    py-8
                    lg:px-8
                    xl:px-10
                    flex
                    items-center
                "
            >

                <div
                    class="
                        w-full
                        max-w-lg
                        mx-auto
                    "
                >


                    <!-- TITLE -->

                    <div class="mb-7">

                        <h1
                            class="
                                text-3xl
                                md:text-4xl
                                font-bold
                                text-gray-800
                            "
                        >
                            Light a Virtual Candle
                        </h1>


                        <p
                            class="
                                text-base
                                text-gray-700
                                mt-2
                            "
                        >
                            Light a candle and let us pray for your intentions.
                        </p>

                    </div>

                    @if (session('status'))
                        <div role="status" aria-live="polite" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif


                    <!-- ================================================= -->
                    <!-- INTENTION TYPE -->
                    <!-- ================================================= -->

                    <div class="mb-4">

                        <label
                            for="intentionType"
                            class="
                                block
                                text-base
                                font-bold
                                text-gray-800
                                mb-2
                            "
                        >
                            Intention Type
                        </label>


                        <select
                            id="intentionType"
                            name="intention_type"
                            aria-label="Intention type"
                            class="
                                input-box
                                w-full
                                px-4
                                py-4
                                rounded-xl
                                text-lg
                                outline-none
                                focus:ring-2
                                focus:ring-blue-200
                            "
                        >

                            <option value="" disabled @selected(! old('intention_type'))>
                                Choose intention type
                            </option>
                            <option value="Thanksgiving" @selected(old('intention_type') === 'Thanksgiving')>
                                Thanksgiving
                            </option>

                            <option value="Healing" @selected(old('intention_type') === 'Healing')>
                                Healing
                            </option>

                            <option value="Birthday" @selected(old('intention_type') === 'Birthday')>
                                Birthday
                            </option>

                            <option value="Souls" @selected(old('intention_type') === 'Souls')>
                                Souls
                            </option>

                            <option value="Special Prayer" @selected(old('intention_type') === 'Special Prayer')>
                                Special Prayer
                            </option>

                        </select>

                    </div>



                    <div id="prayerSection" class="{{ old('intention_type') ? '' : 'hidden' }}">

                    <!-- ================================================= -->
                    <!-- SUGGESTED PRAYER -->
                    <!-- ================================================= -->

                    <div class="mb-5">

                        <label
                            for="prayerText"
                            class="
                                block
                                text-lg
                                font-bold
                                text-gray-800
                                mb-3
                            "
                        >
                            Suggested Prayer
                        </label>


                        <textarea
                            id="prayerText"
                            class="
                                input-box
                                prayer-text
                                w-full
                                px-4
                                py-4
                                rounded-xl
                                text-lg
                                leading-relaxed
                                outline-none
                                resize-none
                                focus:ring-2
                                focus:ring-blue-200
                            "
                        >{{ old('prayer') }}</textarea>

                    </div>



                    <!-- ================================================= -->
                    <!-- PRAYER BUTTONS -->
                    <!-- ================================================= -->

                    <div
                        class="
                            grid
                            grid-cols-1
                            sm:grid-cols-2
                            gap-4
                            mb-8
                        "
                    >

                        <button
                            type="button"
                            onclick="usePrayer()"
                            class="
                                bg-blue-600
                                hover:bg-blue-700
                                text-white
                                font-bold
                                text-lg
                                py-3
                                rounded-lg
                                transition
                            "
                        >
                            Use This Prayer
                        </button>


                        <button
                            type="button"
                            onclick="generatePrayer()"
                            class="
                                outline-button
                                hover:bg-gray-50
                                font-bold
                                text-lg
                                py-3
                                rounded-lg
                                transition
                            "
                        >
                            Generate Another
                        </button>

                    </div>



                    </div>

                    <!-- ================================================= -->
                    <!-- LIGHT CANDLE BUTTON -->
                    <!-- ================================================= -->

                    <button
                        type="button"
                        id="lightButton"
                        onclick="toggleCandle()"
                        class="
                            w-full
                            bg-blue-600
                            hover:bg-blue-700
                            text-white
                            font-bold
                            text-lg
                            py-4
                            rounded-lg
                            transition
                            mb-4
                        "
                    >
                        Light Virtual Candle
                    </button>



                    <!-- ================================================= -->
                    <!-- BACK -->
                    <!-- ================================================= -->

                    <a
                        href="{{ route('dashboard') }}"
                        class="
                            block
                            w-full
                            text-center
                            outline-button
                            hover:bg-gray-50
                            font-bold
                            text-lg
                            py-3
                            rounded-lg
                            transition
                        "
                    >
                        Back To Dashboard
                    </a>



                    <!-- ================================================= -->
                    <!-- PRAYER CONFIRMATION -->
                    <!-- ================================================= -->

                    <div
                        id="prayerMessage"
                        class="
                            hidden
                            mt-6
                            bg-blue-50
                            border
                            border-blue-200
                            rounded-xl
                            px-5
                            py-4
                            text-blue-800
                        "
                    >


                        <p id="selectedPrayer"></p>

                    </div>

                    <form id="candlePrayerForm" method="POST" action="{{ route('virtual-candle.store') }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="intention_type" id="submittedIntentionType">
                        <input type="hidden" name="prayer" id="submittedPrayer">
                        @error('intention_type')
                            <p class="mb-2 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                        @error('prayer')
                            <p class="mb-2 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                        <p id="typeSubmitError" class="mb-2 hidden text-sm text-red-700" role="alert">
                            Choose an intention type first.
                        </p>
                        <p id="candleSubmitError" class="mb-2 hidden text-sm text-red-700" role="alert">
                            Light your candle before submitting your prayer.
                        </p>
                        <button type="submit" class="w-full rounded-lg bg-amber-600 px-5 py-3 font-bold text-white transition hover:bg-amber-700 focus:outline-none focus:ring-4 focus:ring-amber-200">
                            Submit Prayer and View Status
                        </button>
                        <p class="mt-2 text-center text-xs text-gray-500">
                            Your prayer will be saved as pending and appear in
                            <a href="{{ route('users.my-request') }}" class="font-semibold text-blue-700 underline">My Requests</a>
                            for status updates.
                        </p>
                    </form>

                </div>

            </section>

        </div>

    </main>

</div>



<!-- ===================================================== -->
<!-- LOGOUT MODAL -->
<!-- ===================================================== -->

<div
    id="logoutModal"
    class="
        hidden
        fixed
        inset-0
        z-50
        bg-black/40
        items-center
        justify-center
    "
>

    <div
        class="
            bg-white
            w-full
            max-w-md
            mx-4
            rounded-2xl
            shadow-2xl
            p-7
            text-center
        "
    >

        <div
            class="
                mx-auto
                mb-4
                w-14
                h-14
                rounded-full
                bg-red-100
                flex
                items-center
                justify-center
            "
        >

            <span class="text-2xl text-red-600">
                ⇥
            </span>

        </div>


        <h2
            class="
                text-2xl
                font-bold
                text-gray-900
                mb-2
            "
        >
            Logout
        </h2>


        <p class="text-gray-600 mb-7">
            Are you sure you want to log out of your account?
        </p>


        <div class="flex justify-center gap-3">

            <button
                type="button"
                onclick="closeLogoutModal()"
                class="
                    px-6
                    py-2.5
                    rounded-lg
                    border
                    border-gray-300
                    text-gray-700
                    font-semibold
                    hover:bg-gray-100
                "
            >
                Cancel
            </button>


            <button
                type="button"
                onclick="submitLogout()"
                class="
                    px-6
                    py-2.5
                    rounded-lg
                    bg-red-600
                    text-white
                    font-semibold
                    hover:bg-red-700
                "
            >
                Logout
            </button>

        </div>

    </div>

</div>



<script>

    /* ========================================================= */
    /* LOGOUT */
    /* ========================================================= */

    function openLogoutModal() {

        const modal =
            document.getElementById('logoutModal');

        modal.classList.remove('hidden');

        modal.classList.add('flex');

    }


    function closeLogoutModal() {

        const modal =
            document.getElementById('logoutModal');

        modal.classList.add('hidden');

        modal.classList.remove('flex');

    }


    function submitLogout() {

        const form =
            document.getElementById('logoutForm');

        if (form) {

            form.submit();

        }

    }


    document
        .getElementById('logoutModal')
        .addEventListener(
            'click',
            function (event) {

                if (event.target === this) {

                    closeLogoutModal();

                }

            }
        );


    document.addEventListener(
        'keydown',
        function (event) {

            if (event.key === 'Escape') {

                closeLogoutModal();

            }

        }
    );


    /* ========================================================= */
    /* PRAYERS */
    /* ========================================================= */

    const prayers = {

        Thanksgiving: [
            'Lord, thank You for Your abundant blessings, love, and guidance. We are grateful for Your goodness and mercy in our lives. May we always honor You with thankful hearts. Amen.',
            'Heavenly Father, thank You for the gift of another day and for the people who bring joy into our lives. Help us recognize Your blessings and remain grateful. Amen.',
            'Lord, thank You for providing our needs and for guiding us through every challenge. Teach us to appreciate even the smallest blessings. Amen.',
            'Gracious God, we thank You for Your unfailing love, protection, and patience. May our lives reflect our gratitude through kindness and service to others. Amen.',
            'Father, thank You for the opportunities, lessons, and blessings You have given us. Help us use them wisely and share Your goodness with others. Amen.',
            'Lord, we are grateful for our family, friends, and community. Continue to bless our relationships with peace, understanding, and love. Amen.',
            'Heavenly Father, thank You for the strength You give us each day. Help us remain thankful even during difficult moments and trust in Your guidance. Amen.',
            'Lord, thank You for the blessings we sometimes overlook. Open our hearts to see Your goodness in ordinary moments and give thanks always. Amen.',
            'Merciful God, thank You for Your presence in our lives. May gratitude fill our hearts and inspire us to become a blessing to others. Amen.',
            'Lord, with humble hearts we give You thanks for all that You have given us. May we never forget Your goodness and love. Amen.'
        ],

        Healing: [
            'Lord, we ask for Your healing grace upon those who are sick or suffering. Give them strength, comfort, peace, and renewed hope. May Your healing presence guide them each day. Amen.',
            'Merciful Father, place Your healing hands upon those who need comfort and strength. Give them courage, peace, and renewed health according to Your loving will. Amen.',
            'Lord, bring comfort to those who are experiencing pain or illness. Strengthen their bodies, calm their hearts, and surround them with people who care for them. Amen.',
            'Heavenly Father, give healing and hope to those who are struggling with sickness. Help them remain strong in faith and remind them that they are never alone. Amen.',
            'Loving God, grant peace to those who are worried about their health. Give wisdom and guidance to those caring for them and bless them with renewed strength. Amen.',
            'Lord, comfort those who are suffering today. May Your presence bring them peace, courage, and hope as they continue their journey toward healing. Amen.',
            'Father, we place every illness and burden before You. Give those in need the strength to face each day and the comfort of Your loving presence. Amen.',
            'Lord, bless the sick with patience and courage. Guide their doctors, nurses, families, and caregivers, and give everyone strength and wisdom. Amen.',
            'Heavenly Father, bring peace to troubled hearts and healing to those who are suffering. May Your love surround them and give them hope. Amen.',
            'Lord, we trust those who are sick into Your loving care. Give them comfort, strength, and peace, and help them feel the love of those around them. Amen.'
        ],

        Birthday: [
            'Lord, thank You for the gift of life. Bless this birthday with joy, good health, peace, and meaningful moments with family and loved ones. Guide every step of the year ahead. Amen.',
            'Heavenly Father, bless this new year of life with wisdom, happiness, good health, and Your guidance. May every day be filled with love, hope, and gratitude. Amen.',
            'Lord, bless the birthday celebrant with strength, peace, and many joyful moments. May the coming year bring opportunities to grow and serve others. Amen.',
            'Father, thank You for another year of life. May this new chapter be filled with meaningful experiences, loving relationships, and Your constant guidance. Amen.',
            'Loving God, bless this special birthday with happiness and peace. Protect the celebrant and guide every decision in the year ahead. Amen.',
            'Lord, may this birthday be a reminder of Your goodness and faithfulness. Grant good health, wisdom, courage, and many reasons to be grateful. Amen.',
            'Heavenly Father, bless the celebrant and their family with love and peace. May the coming year bring growth, hope, and wonderful memories. Amen.',
            'Lord, thank You for another precious year of life. Help the celebrant use each day wisely and continue to grow in faith, kindness, and love. Amen.',
            'Father, guide the birthday celebrant through the coming year. May Your protection, wisdom, and grace remain with them in every step they take. Amen.',
            'Lord, bless this celebration with joy and gratitude. May the birthday celebrant receive good health, peace, love, and abundant blessings throughout the year. Amen.'
        ],

        Souls: [
            'Lord, we entrust the souls of our departed loved ones into Your loving hands. Grant them eternal rest and comfort those who continue to remember and pray for them. Amen.',
            'Eternal Father, welcome our departed loved ones into Your eternal peace. May their souls rest in Your loving presence, and may their families find comfort and strength through faith. Amen.',
            'Lord, grant eternal rest to those who have gone before us. May their memories remain a source of love and may their families find peace. Amen.',
            'Merciful God, receive the souls of our departed loved ones with compassion. May they find everlasting peace in Your presence. Amen.',
            'Heavenly Father, comfort all who mourn the loss of someone dear. Give them strength to remember their loved ones with gratitude, hope, and peace. Amen.',
            'Lord, may the souls of the departed rest in Your eternal light. Comfort their families and friends and strengthen them in their faith. Amen.',
            'Father, we remember with love those who have passed from this life. Grant them eternal peace and keep their memories alive in our hearts. Amen.',
            'Loving God, welcome the departed into Your heavenly kingdom. May those who mourn find comfort in Your promise of eternal life. Amen.',
            'Lord, grant peace to every family remembering a departed loved one. May their grief be softened by faith, hope, and cherished memories. Amen.',
            'Eternal Father, we place the souls of our departed loved ones in Your care. May they rest peacefully and may their families be surrounded by Your comfort. Amen.'
        ],

        'Special Prayer': [
            'Lord, hear the intentions of our hearts. Guide us with wisdom, strengthen our faith, and help us trust in Your perfect plan. May Your grace remain with us always. Amen.',
            'Loving Father, we place our special intentions before You. Give us patience, courage, and faith as we wait for Your answer. May Your will bring peace to our hearts. Amen.',
            'Lord, guide us in the decisions we must make. Give us wisdom to choose what is right and courage to follow the path that leads us closer to You. Amen.',
            'Heavenly Father, bless our special intentions and every concern we carry in our hearts. Give us peace and help us trust Your timing. Amen.',
            'Lord, be with us in our hopes, worries, and dreams. May Your guidance lead us toward what is good, meaningful, and pleasing to You. Amen.',
            'Father, strengthen our faith when answers seem difficult to find. Help us remain patient and hopeful while trusting in Your loving plan. Amen.',
            'Merciful God, listen to the prayers we quietly carry within our hearts. Give us strength, peace, and the grace to accept Your will. Amen.',
            'Lord, bless our families, relationships, studies, work, and personal intentions. Guide every part of our lives with Your wisdom and love. Amen.',
            'Heavenly Father, give us courage when we face uncertainty and peace when we cannot control the outcome. Help us place our trust in You. Amen.',
            'Lord, receive this special prayer with love and mercy. May Your grace guide us, protect us, and give us peace in whatever lies ahead. Amen.'
        ]

    };


    /* ========================================================= */
    /* CHANGE PRAYER */
    /* ========================================================= */

    document
        .getElementById('intentionType')
        .addEventListener(
            'change',
            function () {

                const type =
                    this.value;

                const prayerText =
                    document.getElementById('prayerText');

                if (prayers[type] && prayers[type].length) {

                    prayerText.value =
                        prayers[type][0];

                }

                /* THE SUGGESTED PRAYER APPEARS ONLY ONCE A TYPE IS CHOSEN */
                document.getElementById('prayerSection').classList.remove('hidden');
                document.getElementById('typeSubmitError').classList.add('hidden');

                /* TURN CANDLE OFF WHEN INTENTION CHANGES */
                const flame =
                    document.getElementById('flame');

                const button =
                    document.getElementById('lightButton');

                candleIsLit = false;

                if (flame) {
                    flame.classList.add('hidden-flame');
                }

                if (button) {
                    button.textContent =
                        'Light Virtual Candle';
                }

            }
        );


    /* ========================================================= */
    /* USE PRAYER */
    /* ========================================================= */

    function usePrayer() {

        const prayer =
            document
                .getElementById('prayerText')
                .value
                .trim();

        const message =
            document
                .getElementById('prayerMessage');

        const selected =
            document
                .getElementById('selectedPrayer');


        if (!prayer) {

            alert('Please enter a prayer intention.');

            return;

        }


        selected.textContent =
            prayer;

        message.classList.remove('hidden');

    }


    /* ========================================================= */
    /* GENERATE ANOTHER PRAYER */
    /* ========================================================= */

    function generatePrayer() {

        const type =
            document
                .getElementById('intentionType')
                .value;

        const prayerList =
            prayers[type] || [];

        const prayerText =
            document
                .getElementById('prayerText');

        if (!prayerList.length) {
            return;
        }

        const current =
            prayerText.value.trim();

        let newPrayer;

        do {

            newPrayer =
                prayerList[
                    Math.floor(
                        Math.random() *
                        prayerList.length
                    )
                ];

        }
        while (
            newPrayer === current &&
            prayerList.length > 1
        );

        prayerText.value =
            newPrayer;

    }


    /* ========================================================= */
    /* CANDLE */
    /* ========================================================= */

    let candleIsLit = false;


    function toggleCandle() {

        const flame =
            document.getElementById('flame');

        const button =
            document.getElementById('lightButton');


        if (!flame || !button) {

            return;

        }


        if (candleIsLit) {

            /* TURN OFF */

            flame.classList.add(
                'hidden-flame'
            );

            button.textContent =
                'Light Virtual Candle';

            candleIsLit = false;

        } else {

            if (!document.getElementById('intentionType').value) {
                document.getElementById('typeSubmitError').classList.remove('hidden');
                document.getElementById('intentionType').focus();
                return;
            }

            /* TURN ON */

            flame.classList.remove(
                'hidden-flame'
            );

            button.textContent =
                'Candle Is Lit';

            candleIsLit = true;

        }

    }

    document
        .getElementById('candlePrayerForm')
        .addEventListener('submit', function (event) {
            const submitError = document.getElementById('candleSubmitError');
            const typeError = document.getElementById('typeSubmitError');

            if (!document.getElementById('intentionType').value) {
                event.preventDefault();
                typeError.classList.remove('hidden');
                document.getElementById('intentionType').focus();
                return;
            }

            typeError.classList.add('hidden');

            if (!candleIsLit) {
                event.preventDefault();
                submitError.classList.remove('hidden');
                document.getElementById('lightButton').focus();

                return;
            }

            submitError.classList.add('hidden');
            document.getElementById('submittedIntentionType').value =
                document.getElementById('intentionType').value;
            document.getElementById('submittedPrayer').value =
                document.getElementById('prayerText').value.trim();
        });


        /* ========================================================= */
    /* SHOW THE PRAYER FOR AN ALREADY-SELECTED TYPE (RELOAD / ERROR) */
    /* ========================================================= */

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const select = document.getElementById('intentionType');
            const prayerText = document.getElementById('prayerText');

            if (select.value) {

                document.getElementById('prayerSection').classList.remove('hidden');

                if (!prayerText.value.trim() && prayers[select.value]) {
                    prayerText.value = prayers[select.value][0];
                }

            }

        }
    );


/* ========================================================= */
    /* INITIAL STATE */
    /* ========================================================= */

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const flame =
                document.getElementById('flame');

            const button =
                document.getElementById('lightButton');


            /*
             * Candle starts turned off.
             */

            candleIsLit = false;


            if (flame) {

                flame.classList.add(
                    'hidden-flame'
                );

            }


            if (button) {

                button.textContent =
                    'Light Virtual Candle';

            }

        }
    );

</script>


</body>
</html>