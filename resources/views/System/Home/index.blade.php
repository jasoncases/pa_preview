<x-layout :loggedIn="$loggedIn" :isAdmin="$isAdmin" :modules="$modules" :statusBox="$statusBox" :user="$user" :rand="$rand" :currentUserInjected="$currentUserInjected">
    <link rel="stylesheet" href="/css/admCodetracker.css" />
    <link rel="stylesheet" href="/css/timesheets.css" />
    <link rel="stylesheet" href="/css/timeline.css" />
    <link rel="stylesheet" href="/css/landing.css" />
    <link rel="stylesheet" href="/css/ticket.css" />
    <link rel="stylesheet" href="/css/task.css" />
    <link rel="stylesheet" href="/css/codes.css" />
    <link rel="stylesheet" href="/css/timer_style.css" />

    <!--  -->
    @if ($loggedIn == 1)
    <div class="landing-container">
        <div class="landing-content-container">
            <div class="landing-row">
                <div class="landing-item dbl-box-shadow flex-col flex-start">
                    <div class="landing-heading">
                        <a href="/timesheet" target="_SELF" style="color: white; text-decoration: none">My Timesheets</a>
                    </div>

                    <div class="landing-content landing-timesheet flex-col flex-start" id="landing-timesheets">
                        <div class="tss-row">
                            <div class="flex-row flex-around col-center pdv-2x w-span" id="buttons-container"></div>
                        </div>
                        <div class="tss-status-row" id="status-container"></div>
                        <div class="tss-row tss-row-flex onBreakDisplay">
                            <div class="tss-row-item tss-row-flex tss-border" id="timeline-container">
                                <div class="flex-col flex-center mg-auto">
                                    <div class="spinner-container" style="padding: 0px">
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
                            <div class="flex-col flex-start tss-row-narrow brd-t1-black">
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
                        </div>
                    </div>
                </div>
                <div class="landing-item flex-col flex-start">
                    <div class="landing-heading">My Codes</div>
                    <div class="landing-content-new flex-1" style="background-color: white">
                        <div class="record-container" id="codes-filter-container">
                            <div class="spinner-container" style="padding: 0px">
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
            </div>
            <div class="landing-row">
                <div class="landing-item dbl-box-shadow">
                    <div class="landing-heading">Company Calendar</div>
                    <div class="landing-content flex-col flex-center" id="landing-calendar">
                        <div class="flex-col flex-center col-center" style="margin-top: -34px">
                            <form method="GET" action="/tardies" class="flex-row flex-center mgb-4x">
                                <button type="submit" class="btn btn-blue pd-4x" @if ($clockedIn==1) disabled @endif>
                                    Running Late
                                </button>
                            </form>
                            <form method="GET" action="/absences" class="flex-row flex-center mgt-4x">
                                <button type="submit" class="btn btn-orange pd-4x" @if ($clockedIn==1) disabled @endif>
                                    Call Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="landing-item flex-col flex-start">
                    <div class="landing-heading">My Tasks</div>
                    <div class="landing-content-new flex-1">
                        <div class="record-container" id="ticket-filter-container">
                            <div class="spinner-container" style="padding: 0px">
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
            </div>
        </div>
    </div>
    @endif
    <script type="module" src="/js/Home/index.js"></script>

    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="/js/package_imports/timer/js/timecircles.min.js"></script>

    </script>
    <script type="module">
        import {
            Timer
        } from '/js/Home/breaktimer.js'
        import {
            User
        } from '/js/User/src/User.js'

        Timer.setContainerId('landing-timesheets')
        User.registerListener(Timer)
    </script>
</x-layout>
