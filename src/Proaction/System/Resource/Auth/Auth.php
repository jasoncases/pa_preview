<?php

namespace Proaction\System\Resource\Auth;

use Illuminate\Database\PDO\PostgresDriver;
use Proaction\Domain\Clients\Resource\ProactionClient;
use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\System\Resource\Cache\Cache;
use Proaction\System\Resource\Cache\ProactionRedis;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Json\Jsonify;
use Proaction\System\Resource\Logger\Log;
use Proaction\System\Resource\Session\UserSession;

class Auth
{

    private $user;
    private $employee;

    public function __construct(ProactionUser $user)
    {
        $this->user = $user;
        $this->employee = EmployeeView::getByUserId($user->id);
    }

    public function loginByUser()
    {

        $this->_setProtoSession();
        $this->_logUserLogin();
        $this->_updateProactionCache();

        session_write_close();

        return $this->_response();
    }

    public function failedLogin()
    {
        return Jsonify::go([
            'status' => 'failure',
        ]);
    }

    public function logout()
    {
        ProactionClient::destroy();
        (new UserSession())->logout();
        $cache = new Cache(ProactionClient::prefix(), ProactionRedis::getInstance());
        // $cache->logOutUser($_SESSION['user']['employeeId']);
        session_destroy();
    }

    private function _setProtoSession()
    {
        $_SESSION['user'] = [
            'userId' => $this->user->id,
            'loggedIn' => 1
        ];
    }

    private function _logUserLogin()
    {
        $this->user->logNewLogin();
    }

    private function _updateProactionCache()
    {
        $cache = new Cache(ProactionClient::prefix(), ProactionRedis::getInstance());
        // $cache->logInUser($this->employee->id);
    }

    private function _response()
    {
        return Jsonify::go([
            'status' => 'success',
            'sesh' => $_SESSION,
            'firstLogin' => false
        ]);
    }

    private function _writeLoginToLog()
    {
        Log::info('Pin login successful', []);
    }
}
