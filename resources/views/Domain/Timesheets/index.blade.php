<x-layout :loggedIn="$loggedIn" :isAdmin="$isAdmin" :modules="$modules" :statusBox="$statusBox" :user="$user" :rand="$rand" :currentUserInjected="$currentUserInjected">



    <link rel="stylesheet" href="/css/timesheets.css?{rand}" />
    <link rel="stylesheet" href="/css/timeline.css?{rand}" />
    <link rel="stylesheet" href="/css/cwt-css.css?{rand}" />
    <link rel="stylesheet" href="/css/timer_style.css" />


    <div class="timesheet-container" id="timesheet-container">

        <div class="sb"></div>
        <div class="login-container">
            <div class="login-item buttons-container" id="buttons-container">
                <!-- BUTTONS GO HERE-->
            </div>
        </div>

        <div class="activity-container">
            <div class="summary" id="status-container">
                <!-- <span id="status-container" class="status-container"></span> -->
            </div>
            <div class="flex-col flex-start tss-row-narrow brd-t1-black mobile">
                <div class="flex-row flex-center text-black text-600 pdv-1x text-14 mobile-vanish">
                    Hours
                </div>
                <div class="flex-row flex-center text-white text-600 pdv-1x pdh-2x text-14 bg-grey">
                    Today
                </div>
                <div class="tss-col-item" id="ui:display:hours:daily:paid">
                    &nbsp;
                </div>
                <div class="flex-row flex-center text-white text-600 pdv-1x pdh-2x text-14 bg-grey">
                    Week
                </div>
                <div class="tss-col-item" id="ui:display:hours:weekly:paid">
                    &nbsp;
                </div>
                <div class="flex-row flex-center text-white text-600 pdv-1x pdh-2x text-14 bg-grey">
                    Month
                </div>
                <div class="tss-col-item" id="ui:display:hours:monthly:paid">
                    &nbsp;
                </div>
            </div>
            <!-- <div class="activity-header">
               </div> -->
            <div class="activity-wrapper">
                <div class="activity-body" id="timeline-container">

                    <div class="spinner-container">
                        <div class="lds-spinner">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="time-off-container standard" id='time-off-container'>
            <div class="to-item hrs" id="weekly-payroll">
                <div class="to-row"><span class="to-header rt-div-line">Current Week:</span><span class="to-header">Current Month:</span></div>
                <div class="to-row"> <span class="to-sub-item rt-al">Total Hours:</span><span class="to-sub-item rt-div-line lt-al" id="ui:display:hours:weekly:total"></span><span class="to-sub-item rt-al">Total Hours:</span><span class="to-sub-item lt-al" id="ui:display:hours:monthly:total"></span></span></div>
                <div class="to-row"> <span class="to-sub-item rt-al">Lunch Hours: </span><span class="to-sub-item rt-div-line lt-al" id="ui:display:hours:weekly:lunch"></span><span class="to-sub-item rt-al">Lunch Hours:</span><span class="to-sub-item lt-al" id="ui:display:hours:monthly:lunch"></span></span></div>
                <div class="to-row"> <span class="to-sub-item rt-al">Break Hours: </span><span class="to-sub-item rt-div-line lt-al" id="ui:display:hours:weekly:break"></span><span class="to-sub-item rt-al">Break Hours:</span><span class="to-sub-item lt-al" id="ui:display:hours:monthly:break"></span></span></div>
                <div class="to-row"> <span class="to-sub-footer rt-al">Paid Hours: </span><span class="to-sub-footer rt-div-line lt-al" id="ui:display:hours:weekly:paid:desktop"></span><span class="to-sub-footer rt-al">Paid Hours:</span><span class="to-sub-footer lt-al" id="ui:display:hours:monthly:paid:desktop"></span></span></div>

                <div id="test"><textarea id="debug" hidden>test</textarea></div>
                <div id="test"></div>
                <div id="test"></div>
                <div id="test"></div>
                <div id="test"></div>
                <div id="test"></div>

            </div>
            <div class="to-item countdown" id="timer-countdown">

            </div>
        </div>

        <div class="rsb"></div>
        <!-- <button class="time-off-container-button" id="tocont">Click For More Info</button> -->
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="/js/package_imports/timer/js/timecircles.min.js"></script>
    <script type="module">
        import {
            Timer
        } from '/js/Home/breaktimer.js'
        import {
            User
        } from '/js/User/src/User.js'

        Timer.setContainerId('timer-countdown')
        User.registerListener(Timer)
    </script>`
</x-layout>
