<x-layout :loggedIn="$loggedIn" :isAdmin="$isAdmin" :modules="$modules" :statusBox="$statusBox" :user="$user" :rand="$rand" :currentUserInjected="$currentUserInjected">
    <style>
        :root {
            --pp-pinpad-width: 400px;
            --pp-pinpad-height: 630px;

            --pp-output-height: 120px;

            --pp-footer-height: 50px;

            --pp-keypad-height: 30px;

            --pp-blip-size: 15px;
            --pp-blip-bd-color: #fff;
            --pp-blip-bg-color: none;
            --pp-blip-bg-selected: rgb(122, 184, 235);

            --pp-btn-color: rgb(60, 100, 133);
            --pp-btn-color-hover: rgb(89, 131, 167);
            --pp-btn-color-action: rgb(126, 157, 185);

            --pp-bg-color: rgb(22, 60, 78);
            font-family: 'Montserrat', sans-serif;
            text-transform: none;
        }

        .pin__container {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            grid-template-rows: repeat(8, 1fr);
            grid-template-areas:
                'sp sp sp sp sp sp lg lg lg lg lg lg'
                'sp sp sp sp sp sp lg lg lg lg lg lg'
                'sp sp sp sp sp sp bp bp bp bp bp bp'
                'sp sp sp sp sp sp ky ky ky ky ky ky'
                'sp sp sp sp sp sp ky ky ky ky ky ky'
                'sp sp sp sp sp sp ky ky ky ky ky ky'
                'sp sp sp sp sp sp ky ky ky ky ky ky'
                'sp sp sp sp sp sp ft ft ft ft ft ft';
            height: 80%;
            width: 80%;
            max-width: 1000px;
            margin: 5% auto;
            background-color: var(--accent-blue);
        }

        .pp-splash {
            grid-area: sp;
            background-image: url("/img/splash.png");
            background-size: cover;
            background-repeat: no-repeat;
        }

        .pp-logo {
            grid-area: lg;
        }

        .pp-blips {
            grid-area: bp;
        }

        .pp-keys {
            grid-area: ky;
        }

        .pp-footer {
            grid-area: ft;
        }

        .pp-keys button {
            -webkit-user-select: none;
            /* Chrome all / Safari all */
            -moz-user-select: none;
            /* Firefox all */
            -ms-user-select: none;
            /* IE 10+ */
            user-select: none;
            /* Likely future */
            /* text-align: center; */
            touch-action: manipulation;
        }

        .pp-keys button:focus {
            outline: none !important;
        }

        @media screen and (max-width: 800px) {
            .pin__container {
                height: 100%;
                width: 100%;
                border: none;
                margin: 0;
                grid-gap: 0;
                grid-template-areas:
                    'lg lg lg lg lg lg lg lg lg lg lg lg'
                    'lg lg lg lg lg lg lg lg lg lg lg lg'
                    'bp bp bp bp bp bp bp bp bp bp bp bp'
                    'ky ky ky ky ky ky ky ky ky ky ky ky'
                    'ky ky ky ky ky ky ky ky ky ky ky ky'
                    'ky ky ky ky ky ky ky ky ky ky ky ky'
                    'ky ky ky ky ky ky ky ky ky ky ky ky'
                    'ft ft ft ft ft ft ft ft ft ft ft ft';
            }

            .pp-splash {
                grid-area: sp;
                border: none;
            }

            .pp-logo {
                grid-area: lg;
                border: none;
            }

            .pp-blips {
                grid-area: bp;
                border: none;
            }

            .pp-keys {
                grid-area: ky;
                border: none;
            }

            .pp-footer {
                grid-area: ft;
            }

            .pp-keys button {
                border: none;
            }
        }

        .blip {
            height: var(--pp-blip-size) !important;
            max-height: var(--pp-blip-size) !important;
            width: var(--pp-blip-size) !important;
            max-width: var(--pp-blip-size) !important;
            border-radius: calc(var(--pp-blip-size) * 0.35);
            border: 2px solid var(--pp-blip-bd-color);
            background-color: var(--pp-blip-bg-color);
            margin: 5px 7px;
        }

        .blip-selected {
            background-color: var(--pp-blip-bg-selected);
        }

        .pinpad-blips {
            display: flex;
            flex-direction: row;
            justify-content: center;
        }

        .blip-shake {
            animation: incorrectPinCode 0.45s ease-in-out;
        }

        @keyframes incorrectPinCode {
            0% {
                transform: scaleX(-10px);
            }

            10% {
                transform: translateX(8px);
            }

            30% {
                transform: translateX(-8px);
            }

            40% {
                transform: translateX(8px);
            }

            50% {
                transform: translateX(-6px);
            }

            60% {
                transform: translateX(6px);
            }

            70% {
                transform: translateX(-3px);
            }

            80% {
                transform: translateX(3px);
            }

            90% {
                transform: translateX(-1px);
            }

            100% {
                transform: translateX(0px);
            }
        }

    </style>

    <div class="pin__container">
        <div class="pp-splash"></div>
        <div class="pp-logo flex-col flex-end col-center">
            <div class="flex-row flex-center text-white text-hero mobile mgb-4x pdb-4x">Proaction</div>
            <div class="flex-row flex-center text-white text-16 mgt-4x">Enter Passcode</div>
        </div>
        <div class="pp-blips flex-col flex-center col-center">
            <div class="flex-row" id="pinpad-blip-container">
                <!--  -->
            </div>
        </div>
        <div class="pp-keys flex-col flex-start">
            <div class="flex-row flex-1 w-span flex-evenly" id="keypad">
                <button type="button" class="flex-1 text-white mg-1x text-24 bg-accent active-dark-85 flex-col flex-center col-center" id="1">1</button>
                <button type="button" class="flex-1 text-white mg-1x text-24 bg-accent active-dark-85 flex-col flex-center col-center" id="2">2</button>
                <button type="button" class="flex-1 text-white mg-1x text-24 bg-accent active-dark-85 flex-col flex-center col-center" id="3">3</button>
            </div>
            <div class="flex-row flex-1 w-span flex-evenly">
                <button type="button" class="flex-1 text-white mg-1x text-24 bg-accent active-dark-85 flex-col flex-center col-center" id="4">4</button>
                <button type="button" class="flex-1 text-white mg-1x text-24 bg-accent active-dark-85 flex-col flex-center col-center" id="5">5</button>
                <button type="button" class="flex-1 text-white mg-1x text-24 bg-accent active-dark-85 flex-col flex-center col-center" id="6">6</button>
            </div>
            <div class="flex-row flex-1 w-span flex-evenly">
                <button type="button" class="flex-1 text-white mg-1x text-24 bg-accent active-dark-85 flex-col flex-center col-center" id="7">7</button>
                <button type="button" class="flex-1 text-white mg-1x text-24 bg-accent active-dark-85 flex-col flex-center col-center" id="8">8</button>
                <button type="button" class="flex-1 text-white mg-1x text-24 bg-accent active-dark-85 flex-col flex-center col-center" id="9">9</button>
            </div>
            <div class="flex-row flex-1 w-span flex-evenly">
                <button type="button" class="flex-1 text-white mg-1x text-24 bg-accent active-dark-85 flex-col flex-center col-center" id="delete">&#8656</button>
                <button type="button" class="flex-1 text-white mg-1x text-24 bg-accent active-dark-85 flex-col flex-center col-center" id="0">0</button>
                <button type="button" class="flex-1 text-white mg-1x text-24 bg-accent active-dark-85 flex-col flex-center col-center" id="submit">OK</button>
            </div>
        </div>
        <div class="pp-footer flex-col flex-end col-right pd-2x">
            <a href="/email_login" class="text-16 text-white text-400">Log in with email</a>
        </div>
    </div>
    <script type="module" src="/js/Pinpad/index.js?{{$rand}}"></script>
    <script type="module">
        import {
            Fetch
        } from '/js/System/Components/Fetch/Fetch.js'


        const a = {
            foo: "bar",
            fizz: "buzz",
        }

        Fetch.store('/api/sess', a).then(r => console.log(r))
    </script>

</x-layout>
