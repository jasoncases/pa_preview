<?php

namespace Proaction\System\Controller;

use Proaction\System\Model\AccessLog\AccessLog;
use Proaction\System\Resource\Timezone\ProactionTimezone;

class BaseApiProactionController extends BaseProactionController
{


    /**
     * >>> The difference here is that callAction does NOT have a login
     * >>> requirement, to allow us to display some information to the
     * >>> "GUEST" user, i.e. voice announcements. This is likely not
     * >>> ideal, and will probably conflict with the Laravel User/login
     * >>> cycle, but again, this was done to quickly convert the old
     * >>> framework to Laravel and can be changed as needed.
     *
     * Execute an action on the controller.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {

        /*
        |----------------------------------------------------------------------
        | Set The Timezone
        |----------------------------------------------------------------------
        |
        | Laravel didn't have a good way to easily change the timezone
        | based on a user setting, or at least not one that I could
        | find with a quick google search, so this is a class that does
        | this one thing, setting the tz based on a value in the
        | `client_globals` database table. There is a Laravel package,
        | but it sets the tz based on the users login geolocated IP, &
        | I think it's more likely that we want to set it based on the
        | CLIENT administration's preferences, so that's what this does
        |
        */
        ProactionTimezone::set_client_default();

        /*
        |----------------------------------------------------------------------
        | Log User Access
        |----------------------------------------------------------------------
        |
        | Store a new record in the `system_access_log` table. The base
        | controller is false by default, so all controllers need to
        | opt in by changing the logAccess variable. The logAccess bool
        | value is given as an attr and AccessLog will log if true and
        | ignore if false
        |
        */
        AccessLog::controllerLog($this->logAccess);

        /*
        |----------------------------------------------------------------------
        | Display Login Views
        |----------------------------------------------------------------------
        |
        | Sets some default display properties, using the Data::class
        |
        */
        $this->_preloadDisplayProperties();

        // defaul Laravel action caller
        return $this->{$method}(...array_values($parameters));
    }
}
