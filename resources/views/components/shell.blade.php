</head>

<body>


    <div class="wrapper" id="wrapper">
        <div class="header" id="header">
            <!--Include Header Nav? -->
            <x-user_header :user="$user" :user="$user" :rand="$rand"></x-user_header>
        </div>
        <div class="mobileMenu" id="mobileMenu">
            <!--  -->
            <div class="mobileMenu-heading">
                <!--  -->
                <div class="toggle-menu" id="toggle-menu">
                    <div class="tm-line"></div>
                    <div class="tm-line"></div>
                    <div class="tm-line"></div>
                </div>
                <!--  -->
                <div class="mobileMenu-launcher flex-col flex-center col-center flex-1" id="ui:cwt:launcher:mobile">
                    @if ($user->loggedIn == 1)
                    <div id="CWT-icon-mobile" style="font-size: 18px;">
                        <span style="font-size:1.6em; pointer-events: none;"><i class="fas fa-arrow-alt-square-down"></i></span>
                    </div>
                    @endif
                </div>
                <!--  -->
                <div class="text-white text-600 text-16 pdt-1x"> Proaction </div>
                <!--  -->
                <div class="mobileMenu-launcher flex-col flex-center col-center flex-1">
                    @if ($user->loggedIn == 1)
                    <div id="snapshot-Launcher">
                        <span id="snapshotHitpointMobile">
                            <i class="fas fa-user" style="font-size:16px; margin-right: -5px;"></i>
                            <span>
                                <i class="fas fa-user" style="font-size:22px; margin-right: -5px;"></i>
                            </span>
                            <i class="fas fa-user" style="font-size:16px;"></i>
                        </span>
                        <!-- <span style="font-size: 1.2rem; margin: 18px 5px;" id="snapshotHitpointMobile"><i class="fas fa-users" style="font-size: 26px;"></i></span>        -->
                    </div>
                    @endif
                </div>
                <!--  -->
                <div class="toggle-settings" id="toggle-settings">
                    @if ($user->loggedIn == 1)
                    <a href="/user_profile/{{$user->get('userId')}}">
                        <img src="/img/gear.png" style="height:25px;width:25px;" />
                    </a>
                    @endif
                </div>
                <!--  -->
            </div>
            <!--  -->
            <div class="mobileMenu-links-container menu-collapse" id="mobileMenuInner">
                <ul class="links" id="links-container">
                    @if ($user->loggedIn == 1)
                    <li class="menu-btn"><a href="/landing" class="sidebar-link" id="sbl-1">
                            <div class="sidebar-icon"><i class="fas fa-home-alt"></i></div><span class="sidebar-text">Dashboard</span>
                        </a></li>
                    <li class="menu-btn"><a href="/timesheet" class="sidebar-link" id="sbl-2">
                            <div class="sidebar-icon"><i class="far fa-clock"></i></div><span class="sidebar-text">Timesheets</span>
                        </a></li>
                    <li class="menu-btn"><a href="/schedule" class="sidebar-link" id="sbl-3">
                            <div class="sidebar-icon"><i class="far fa-stream"></i></div><span class="sidebar-text">Schedule</span>
                        </a></li>
                    <li class="menu-btn"><a class="sidebar-link" href="https://calendar.google.com/calendar/r" target="_blank">
                            <div class="sidebar-icon"><i class="fal fa-calendar-alt"></i></div><span class="sidebar-text">Company Calendar</span>
                        </a></li>
                    <li class="menu-btn"><a href="/tasks/create" class="sidebar-link" id="sbl-4">
                            <div class="sidebar-icon"><i class="far fa-tasks"></i></div><span class="sidebar-text">Task Manager</span>
                        </a></li>
                    <li class="menu-btn"><a href="/codes/create" class="sidebar-link" id="sbl-5">
                            <div class="sidebar-icon">
                                <i class="far fa-crosshairs"></i>
                            </div><span class="sidebar-text">Code Tracker</span>
                        </a></li>
                    <li class="menu-btn"><a class="sidebar-link" href="/voice" id="sbl-8">
                            <div class="sidebar-icon"><i class="fas fa-volume"></i></div><span class="sidebar-text">Voice Announcements</span>
                        </a></li>
                    <li class="menu-btn"><a href="https://drive.google.com/open?id=0ByeFPGyUM_kyOXRSaWwwdzdWSEk" target="_blank" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fal fa-ruler-triangle"></i></div><span class="sidebar-text">Protocols</span>
                        </a></li>
                    <li class="menu-btn"><a class="sidebar-link" href="/report" target="_SELF" id="sbl-9">
                            <div class="sidebar-icon"><i class="far fa-file-chart-line"></i></div><span class="sidebar-text">Reports</span>
                        </a></li>
                    @endif
                    <!--  -->
                    @if ($user->isAdministrator() == true)
                    <!--  -->
                    <li class="menu-btn">
                        <a class="sidebar-link" href="/employees" target="_SELF" id="sbl-10">
                            <div class="sidebar-icon">
                                <i class="fas fa-users-class"></i>
                            </div>
                            <span class="sidebar-text">Human Resources</span>
                        </a>
                    </li>
                    @endif
                    <!--  -->
                    @if ($user->loggedIn == 1)
                    <!-- TODO IF LOGGED IN -->
                    <li class="menu-btn">
                        <a class="sidebar-link" href="/module" id="sbl-11">
                            <div class="sidebar-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <span class="sidebar-text">Modules</span>
                        </a>
                    </li>
                    <li class="menu-btn">
                        <a class="sidebar-link" href="/tickets/create" id="sbl-12">
                            <div class="sidebar-icon">
                                <i class="far fa-comment-alt-exclamation"></i></i>
                            </div>
                            <span class="sidebar-text">Support Ticket</span>
                        </a>
                    </li>
                    @endif
                    <!-- BEGIN LOGIN  -->
                    @if ($user->loggedIn != 1)
                    <li class="menu-btn">
                        <a href="/login" target="_SELF" class="sidebar-link btn-action">
                            <div class="sidebar-icon">
                                <i class="far fa-sign-in"></i>
                            </div>
                            <span class="sidebar-text">Login</span>
                        </a>
                    </li>
                    @else
                    <li class="menu-btn btn-danger">
                        <a href="/logout" target="_SELF" class="sidebar-link">
                            <div class="sidebar-icon">
                                <i class="far fa-sign-in"></i>
                            </div>
                            <span class="sidebar-text">Logout</span>
                        </a>
                    </li>
                    @endif
                    <!--  -->
                </ul>
            </div>
        </div>
        <div class="sidebar {sidebar_collapsed || }" id="sidebar">
            <ul class="links" id="links-container">
                @if ($user->loggedIn == 1)
                <li class="menu-btn">
                    <a href="/landing" class="sidebar-link" id="sbl-1">
                        <div class="sidebar-icon">
                            <i class="fas fa-home-alt"></i>
                        </div>
                        <span class="sidebar-text sidebar-tooltip" data-text="Dashboard">Dashboard</span>
                    </a>
                </li>
                <li class="menu-btn">
                    <a href="/timesheet" class="sidebar-link" id="sbl-2">
                        <div class="sidebar-icon">
                            <i class="far fa-clock"></i>
                        </div>
                        <span class="sidebar-text sidebar-tooltip">Timesheets</span>
                    </a>
                </li>
                <li class="menu-btn">
                    <a href="/schedule" class="sidebar-link" id="sbl-3">
                        <div class="sidebar-icon">
                            <i class="far fa-stream"></i>
                        </div>
                        <span class="sidebar-text sidebar-tooltip">Schedule</span>
                    </a>
                </li>
                <li class="menu-btn">
                    <a class="sidebar-link" href="https://calendar.google.com/calendar/r" target="_blank">
                        <div class="sidebar-icon">
                            <i class="fal fa-calendar-alt"></i>
                        </div>
                        <span class="sidebar-text sidebar-tooltip">Company Calendar</span>
                    </a>
                </li>
                <li class="menu-btn">
                    <a href="/tasks_gui" class="sidebar-link" id="sbl-4">
                        <div class="sidebar-icon">
                            <i class="far fa-tasks"></i>
                        </div>
                        <span class="sidebar-text">Task Manager</span>
                    </a>
                    <ul class="sidebar-dropdown">
                        <li class="sidebar-sub-row">
                            <a href="/tasks_gui" target="_SELF">
                                Home
                            </a>
                        </li>
                        <li class="sidebar-sub-row">
                            <a href="/task_user_lists" target="_SELF">
                                My Tasks
                            </a>
                        </li>
                        <li class="sidebar-sub-row">
                            <a href="/tasks" target="_SELF">
                                Task Manager Classic
                            </a>
                        </li>
                        <li class="sidebar-sub-row">
                            <a href="/tasks/create" target="_SELF">
                                Create New Task
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

                @if ($user->loggedIn == 1)
                <li class="menu-btn">
                    <a href="/codes" class="sidebar-link" id="sbl-6">
                        <div class="sidebar-icon">
                            <i class="far fa-crosshairs"></i>
                        </div>
                        <span class="sidebar-text">Code Tracker</span>
                    </a>
                    <ul class="sidebar-dropdown">
                        <li class="sidebar-sub-row">
                            <a href="/codes" target="_SELF">
                                Home
                            </a>
                        </li>
                        <li class="sidebar-sub-row">
                            <a href="/codes/create" target="_SELF">
                                Create new code </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-btn">
                    <a class="sidebar-link" href="/voice" id="sbl-8">
                        <div class="sidebar-icon">
                            <i class="fas fa-volume"></i>
                        </div>
                        <span class="sidebar-text sidebar-tooltip">Voice Announcements</span>
                    </a>
                </li>
                <li class="menu-btn">
                    <a href="https://drive.google.com/open?id=0ByeFPGyUM_kyOXRSaWwwdzdWSEk" target="_blank" class="sidebar-link">
                        <div class="sidebar-icon">
                            <i class="fal fa-ruler-triangle"></i>
                        </div>
                        <span class="sidebar-text sidebar-tooltip">Protocols</span>
                    </a>
                </li>
                <li class="menu-btn">
                    <a class="sidebar-link" href="/payroll" target="_SELF" id="sbl-9">
                        <div class="sidebar-icon">
                            <i class="far fa-file-chart-line"></i>
                        </div>
                        <span class="sidebar-text sidebar-tooltip">Reports</span>
                    </a>
                </li>
                @endif
                <!--  -->
                @if ($user->isAdministrator() == 1)
                <li class="menu-btn">
                    <a class="sidebar-link" href="/employees" target="_SELF" id="sbl-10">
                        <div class="sidebar-icon">
                            <i class="fas fa-users-class"></i>
                        </div>
                        <span class="sidebar-text sidebar-tooltip">Human Resources</span>
                    </a>
                </li>
                @endif
                <!--  -->
                @if ($user->loggedIn == 1)
                <li class="menu-btn">
                    <a class="sidebar-link" href="/module" id="sbl-11">
                        <div class="sidebar-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <span class="sidebar-text">Modules</span>
                        <span class="popout-menu"></span>
                    </a>
                    <ul class="sidebar-dropdown">
                        @foreach ($modules as $module)
                        <li class="sidebar-sub-row">
                            <a href="{{$module['link']}}" target="{{$module['target']}}">
                                {{$module['name']}}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </li>

                <li class="menu-btn">
                    <a class="sidebar-link" href="/tickets" id="sbl-12">
                        <div class="sidebar-icon"><i class="far fa-comment-alt-exclamation"></i></div><span class="sidebar-text">Support Ticket</span>
                    </a>
                    <ul class="sidebar-dropdown">
                        <li class="sidebar-sub-row">
                            <a href="/tickets" target="_SELF">Home</a>
                        </li>
                        <li class="sidebar-sub-row">
                            <a href="/tickets/create" target="_SELF">Create new ticket</a>
                        </li>
                    </ul>
                </li>
                @endif
                <!--  -->
                @if ($user->loggedIn != 1)
                <li class="menu-btn"><a href="/login" target="_SELF" class="sidebar-link btn-action">
                        <div class="sidebar-icon"><i class="far fa-sign-in"></i></div><span class="sidebar-text">Login</span>
                    </a></li>
                @endif
                <!--  -->
                <li class="menu-btn">
                    <a id="ui:collapse" class="sidebar-link">
                        <div class="sidebar-icon ui-collapse text-grey"><i class="far fa-arrow-to-left"></i></div>
                        <span class="sidebar-text text-grey">Collapse</span>
                    </a>
                </li>

            </ul>
        </div>
        <div class="flash-status dbl-box-shadow" id="flash-status">
            <div class="flash-text" id="flash-text"></div>
        </div>
        <div id="loader-anchor" style="height:0;width:0;position:absolute;left:0;top:0"></div>
        <div data-id="component-anchor" id="component-anchor" style="height:0;width:0;position:absolute;left:0;top:0"></div>
        <script type="text/javascript">
            const slbTarget = 'sbl-{linkId}';
            const activeMenuItem = document.querySelectorAll(`[id="${slbTarget}"]`);
            if (activeMenuItem) {
                activeMenuItem.forEach((el) => {
                    el.classList.add('menu-ilnk-selected');
                });
            }

        </script>
        <div class="content" id="content">

            <x-status_message :statusBox="$statusBox"></x-status_message>

            <!-- END UPPER SHELL -->
