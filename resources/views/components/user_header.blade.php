<div class="usernav">
    <div class="logo"><img src="/img/jclogo.png" class="logo-img" /></div>
    <div class="usernav-wrap flex-1 flex-col flex-start">
        <div class="flex-row flex-around flex-1">
            <!-- Employee snapshot toggle icon -->
            @if ($user->loggedIn == 1)
            <span style="font-size: 16px;margin:0 16px" id="snapshotHitpoint">
                <i class="fas fa-user" style="font-size: 16px;margin-right:-5px"></i>
                <span style="font-size: 20px;margin-right:-6px">
                    <i class="fas fa-user"></i>
                </span>
                <i class="fas fa-user" style="font-size: 16px;margin-right:-5px"></i>
            </span>

            <!-- Audio control panel, should minimze this and allow for dropdown -->
            <div class="audio-controls" id="audio-controls">
                <!-- <button
          class="btn btn-transparent btn-hover-darken btn-hover-grow"
          id="audioVolUp"
        >
          <i class="fas fa-volume-up"></i>
        </button> -->
                <button class="btn btn-transparent btn-hover-darken btn-hover-grow voice-btn" id="ui:voice:player:mute">
                    <i class="fas fa-volume-mute" style="font-size: 20px"></i>
                </button>
                <button class="btn btn-transparent btn-hover-darken btn-hover-grow voice-btn" id="audioReplay" style="display: block; margin-top: auto">
                    <embed src="/img/audio_replay.svg" width="20" height="20" type="image/svg+xml" codebase="http://www.adobe.com/svg/viewer/install" />
                </button>
            </div>
            @endif

            <!-- iframe view of audio loader -->
            <div class="audio-loader" id="audio-loader"></div>

            <!-- Custom work type launcher dropdown menu -->
            @if ($user->loggedIn == 1)
            <div class="cwt-launcher flex-row flex-start" id="ui:cwt:launcher:desktop">
                <div class="cwt-icon" id="CWT-icon">
                    <i class="fas fa-arrow-alt-square-down"></i>
                </div>
                <div class="flex-col flex-center text-left cwt-status standard" id="CWT-status-container" style="flex: none; width: fit-content"></div>
            </div>

            <!-- User status -->
            <div class="log-status flex-row flex-end" id="log-status">
                <ul class="user-menu flex-col flex-start text-right flex-1">
                    <li>
                        <span>Logged in as </span>
                        <span class="username" id="ui:nav:username">{{$user->get('firstName')}} {{$user->get('lastName')}}</span>
                    </li>
                    <li class="flex-row flex-end">
                        <ul class="flex-col flex-start user-menu-links dbl-box-shadow">
                            <li><a href="/user_profile/{{$user->get('userId')}}">Profile</a></li>
                            <li><a href="">Account Settings</a></li>
                            <li><a href="/logout" class="btn-danger">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>
