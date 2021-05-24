<?php

namespace Proaction\Domain\Permissions\Resource;

use Closure;
use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\Domain\Users\Resource\UserFactory;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Logger\Log;
use Proaction\System\Resource\Status\FlashAlert;

class PermissionCheck
{

    private $User;
    private $Uri;
    private $Time;

    public function __construct()
    {
        $this->_init();
    }

    public function handle($request, Closure $next, $permissionLevel)
    {
        $response = $next($request);

        if (!PermissionCheck::mw_validate($permissionLevel)) {
            new FlashAlert("You do not have the necessary permissions to view the requested page - Returned to home");
            return redirect('/');
        }

        return $response;
    }

    private static function mw_validate($permissionLevel)
    {
        return (new static)->_mw_validate($permissionLevel);
    }

    private function _mw_validate($permissionLevel)
    {
        $user = UserFactory::create();
        return boolval($user->getPermission($permissionLevel));
    }

    private function _init()
    {
        $this->user = UserFactory::create();
        $this->time = date('Y-m-d H:i:s');
    }


    public static function validate($permissionCheckCode, $action = 'redirect')
    {
        return (new static)->_validate($permissionCheckCode, $action);
    }

    private function _validate(string $code, string $action)
    {
        if (!boolval($this->user->getPermission($code))) {
            return $this->_onPermissionFailure($action);
        }
        return true;
    }

    private function _onPermissionFailure(string $action)
    {
        $this->_logFailedAccess();
        return $this->_createAction($action);
    }

    private function _logFailedAccess()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $user = UserFactory::create();
        $userName = $user->get('firstName') . ', ' . $user->get('email');
        $time = date('Y-m-d H:i:s');
        Log::warning(
            "Access denied to [ $uri ] - User: $userName at $time"
        );
    }

    private function _createAction($action)
    {
        switch ($action) {
            case 'redirect':
                return redirect('/');
            default:
                return;
        }
    }
}
