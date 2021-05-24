<?php

namespace Proaction\System\Controller;

use App\Http\Controllers\Controller;
use Proaction\Domain\Users\Resource\UserFactory;
use Proaction\System\Model\AccessLog\AccessLog;
use Proaction\System\Resource\Data\Data;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Session\SystemSession;
use Proaction\System\Resource\Timezone\ProactionTimezone;
use Proaction\System\Views\HomeIndexView;

/**
 * Extending the base Laravel Controller class.
 *
 **/
class BaseProactionController extends Controller
{

    protected $linkId;

    /*
    |--------------------------------------------------------------------------
    | Log User Controller Access
    |--------------------------------------------------------------------------
    |
    | Set full controller level logging for the user. By default logs
    | HTTP_REFERER, REQUEST_METHOD, REQUEST_URI, and employee_id as
    | 'author' field. Can be called in methods, see below, if full
    | controller log is not desired, and/or a controller hasn't been
    | split into more isolated classes yet.
    |
    | See `TimesheetActionsController` for an example of AccessLog being
    | used at the method level.
    |
    */
    protected $logAccess = false;

    /**
     * ! Proaction overwrites some behavior here. This can be changed,
     * ! but was simply put in place to make a fast transition from the
     * ! previous framework to work with Laravel. Explanations are below
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
        | Create The Current User
        |----------------------------------------------------------------------
        |
        | If there is a live session, an EmployeeClass object is created
        | otherwise, a NullUser obj is returned
        |
        */
        $user = UserFactory::create();

        /*
        |----------------------------------------------------------------------
        | Display Login Views
        |----------------------------------------------------------------------
        |
        | One thing to do would be to convert the old Proaction login
        | cycle to the default Laravel one. This sets redirect value and
        | displays the login views
        |
        */
        if (!$user->isLoggedIn()) {
            (new SystemSession)->add('REDIRECT', $_SERVER['REDIRECT_URL']);
            return $this->_displayLogin();
        }

        /*
        |----------------------------------------------------------------------
        | Log User Access
        |----------------------------------------------------------------------
        |
        | Store a new record in the `system_access_log` table. The base
        | controller is false by default, so all controllers need to
        | opt in by changing the logAccess variable. The $logAccess bool
        | value is given as an attr and AccessLog will log if true and
        | ignore if false
        |
        */
        AccessLog::controllerLog($this->logAccess);

        /*
        |----------------------------------------------------------------------
        | Display default properties for every view
        |----------------------------------------------------------------------
        |
        | Sets some default display properties, using the Data::class
        |
        */
        $this->_preloadDisplayProperties();

        // finally, run the default Laravel action caller
        return $this->{$method}(...array_values($parameters));
    }

    /**
     *
     * @return \view
     */
    protected function _displayLogin()
    {
        if (isset($_GET['loginWithEmail']) && boolval($_GET['loginWithEmail']) === true) {
            return view('System.Home.login', HomeIndexView::add());
        }
        return view('System.Home.pinpad', HomeIndexView::add());
    }

    /**
     * Add an item to the Data display property. This will be loaded
     * into the ViewBuilder
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function _addDisplayAttribute($key, $value)
    {
        Data::add($key, $value);
    }

    /**
     * Set all preloaded display props
     *
     * @return void
     */
    protected function _preloadDisplayProperties()
    {
        $this->_addDisplayAttribute('linkId', $this->linkId);
    }
}
