<?php

namespace Proaction\System\Resource\Timezone;

use Proaction\Domain\Clients\Model\GlobalSetting;

class ProactionTimezone
{
    /**
     * Set the default timezone based on a Client setting.
     *
     * * Reminder: GlobalSetting::class loads a value once per session.
     * * Once it has been loaded, it is stashed to SystemSession::class
     * * and pulled from there until the user logs out. This was done to
     * * cut down on DB queries
     *
     * @return void
     */
    public static function set_client_default()
    {
        date_default_timezone_set(GlobalSetting::get('timezone'));
    }
}
