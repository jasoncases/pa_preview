<x-layout :loggedIn="$loggedIn" :isAdmin="$isAdmin" :modules="$modules" :statusBox="$statusBox" :user="$user" :rand="$rand" :currentUserInjected="$currentUserInjected">


    <div class="up-container">
        <!-- <custom-select label="Select Something Dammit" class="btn-danger" sdstyle="backgroundColor:black;border:1px solid blue;"></custom-select> -->

        <div class="up-row">
            <div class="up-row-primary" id="up-row">
                <div class="up-icon-item"><i class="fas fa-user-edit fa-4x"></i></div>

                <div class="up-summary-item">
                    <div class="up-summary-heading">User Profile Settings</div>
                    <div class="up-summary-summary">Change your nickname, update your email</div>
                </div>
            </div>
            <div class="up-row-secondary collapse">
                <div class="up-row-secondary-blank"></div>
                <div class="up-row-secondary-container">
                    <div class="up-row-heading"></div>
                    <div class="up-row-content">
                        <div class="up-row-content-row">
                            <div class="up-row-content-item up-row-content-item-heading">
                                Edit Availability:
                            </div>
                            <div class="up-row-content-item up-row-content-item-content">
                                <span><a href="/availability/{employeeId}/edit" style="display:inline;">Click here</a> to update your availability</span>
                            </div>
                        </div>
                        <div class="up-row-content-row">
                            <div class="up-row-content-item up-row-content-item-heading">
                                Edit Requests:
                            </div>
                            <div class="up-row-content-item up-row-content-item-content">
                                <span><a href="/request/{employeeId}/edit" style="display:inline;">Click here</a> to update your requests</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BEGIN PRIMARY ROW-->
        <div class="up-row">
            <div class="up-row-primary" id="up-row">
                <div class="up-icon-item"><i class="fas fa-cogs fa-4x"></i></div>

                <div class="up-summary-item">
                    <div class="up-summary-heading">Account Settings</div>
                    <div class="up-summary-summary">Reset your password or PIN, change login settings</div>
                </div>
            </div>
            <!-- END PRIMARY -->
            <div class="up-row-secondary collapse">
                <div class="up-row-secondary-blank"></div>
                <div class="up-row-secondary-container">
                    <div class="up-row-heading"></div>
                    <div class="up-row-content">
                        <div class="up-row-content-row">
                            <div class="up-row-content-item up-row-content-item-heading">
                                Update PIN Code:
                            </div>
                            <div class="up-row-content-item up-row-content-item-content">
                                <span><a href="/user/{{$user->get('userId')}}/edit/pin/" style="display:inline;">Click here</a> to update your pin access code.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BEGIN PRIMARY ROW-->
            <div class="up-row">
                <div class="up-row-primary" id="up-row">
                    <div class="up-icon-item"><i class="fas fa-cogs fa-4x"></i></div>

                    <div class="up-summary-item">
                        <div class="up-summary-heading">Globals</div>
                        <div class="up-summary-summary">// testing</div>
                    </div>
                </div>
                <!-- END PRIMARY -->
                <div class="up-row-secondary collapse">
                    <div class="up-row-secondary-blank"></div>
                    <div class="up-row-secondary-container">
                        <div class="up-row-heading"></div>
                        <div class="up-row-content">
                            <div class="up-row-content-row">
                                <div class="up-row-content-item up-row-content-item-heading"></div>
                                <div class="up-row-content-item up-row-content-item-content">
                                    @if ($isAdmin == 1)
                                    <span><a href="/settings" style="display:inline;">Go to Settings Page</a></span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END ROW -->

            </div>
            <!-- END ROW -->
        </div>
        <div class="flex-col flex-end mg-4x">
            @if ($isSuper == 1)
            <a href="/admin">Proaction Super Admin Dashboard</a>
            @endif
        </div>
    </div>

    <script type="text/javascript">
        const rows = document.querySelectorAll('#up-row')
        console.log('rows:', rows)
        rows.forEach(row => {
            row.addEventListener('click', e => {
                const next = row.nextElementSibling;
                next.classList.toggle('collapse');
            });
        });

    </script>


    </div>
    </div>
    </div>


</x-layout>
