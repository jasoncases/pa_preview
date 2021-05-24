<?php

namespace Proaction\Domain\Users\Resource;

use Proaction\Domain\Permissions\Model\PermissionLevels;
use Proaction\System\Resource\Session\UserSession;
use Illuminate\Support\Str;

/**
 * Singleton because there can only be one active user at a time, any
 * subsequent calls will result in the original user being returned.
 *
 */
abstract class BaseUserProaction
{

    /**
     * EmployeeView Model
     *
     * @var EmployeeView
     */
    protected $identity;

    protected static $instance;
    protected $type;

    protected $userId;

    /**
     * User display and communication data, email, sms, fullname, etc
     *
     * @var array
     */
    protected $personalData;

    /**
     * loggedIn, isAdmin, isSuperAdmin
     *
     * @var array
     */
    protected $authState = [];

    /**
     * clockedIn, status
     *
     * @var array
     */
    protected $timeclockState = [];

    /**
     * Holds permission data, via key:str => bool associative array.
     * Permissions are explicit toggles giving a user access to actions
     * throughout the application
     *
     * @var array
     */
    protected $permissions = [];

    protected $forcePasswordReset = false;

    protected $sessionToken;

    protected $session;

    private function __construct()
    {
        $this->session = new UserSession();
        $this->create($this->_getUserId());
    }

    public function destroy() {
        $this->session->kill();
        $this->timeclockState = null;
        $this->authState = null;
        $this->permissions = null;
        $this->personalData = null;
        $this->identity = null;
        $this->userId = null;
        $this->type = null;
    }

    protected function _getUserId() {
        return $_SESSION['user']['userId'] ?? null;
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = (new static);
        }
        return self::$instance;
    }

    public function getType()
    {
        return $this->type;
    }

    public function create($userId = null)
    {
        $this->userId = $userId;
        $this->_init();
        $this->_setPersonalData();
        $this->_setTopLevelProps();
        $this->_setAuthState();
        $this->_loadPermissions();
        $this->_setTimeclockState();
        $this->_logUserToSession();
        return $this;
    }

    abstract protected function _init();
    abstract protected function _setPersonalData();

    protected function _setTopLevelProps()
    {
        $this->_setIsAdmin();
        $this->_setIsLoggedIn();
    }

    private function _setIsAdmin()
    {
        $this->isAdmin = $this->isAdministrator();
    }

    private function _setIsLoggedIn()
    {
        $this->loggedIn = $this->isLoggedIn();
    }

    abstract protected function _setAuthState();
    abstract protected function _setTimeclockState();
    abstract protected function _loadPermissions();

    protected function _logUserToSession()
    {
        if ($this->isLoggedIn()) {
            $_SESSION['user'] = array_merge(
                $_SESSION['user'],
                ['permissions' => $this->permissions],
                $this->_getDisplayProps(),
            );
        }
    }

    /**
     * a wrapper for display props. Was necessary on the old framework,
     * may not be with the differences in laravel's blade engine
     *
     * @return void
     */
    public function getDisplayProps()
    {
        return $this->_getDisplayProps();
    }

    public function getSerializedJSONDisplayProps()
    {
        return json_encode($this->getDisplayProps());
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    protected function _getDisplayProps()
    {
        return [
            'employeeId' => $this->get('employeeId'),
            'userId' => $this->get('userId'),
            'loggedIn' => $this->isLoggedIn(),
            'firstName' => $this->get('firstName'),
            'status' => $this->get('status'),
            'lastName' => $this->get('lastName'),
            'email' => $this->get('email'),
            'nickname' => $this->get('nickname'),
            'fullName' => $this->get('fullDisplayName'),
            'display' => $this->get('displayName'),
            'isAdmin' => $this->isAdministrator(),
            'clockedIn' => $this->timeclockState['isClockedIn'] ?? null,
            'isSuperAdmin' => $this->isSuperAdmin(),
        ];
    }

    /**
     * Get employee personal data from the EmployeeView::class
     *
     * @param string $key   - snakeCase (employeeId, userId, firstName..
     * @return mixed
     */
    public function get($key)
    {

        return $this->personalData[$key] ?? null;
    }

    public function getEmployeeId()
    {
        return $this->get('employeeId');
    }

    public function getUserId()
    {
        return $this->get('userId');
    }

    public function getEmail()
    {
        return $this->get('email');
    }

    public function isAdministrator()
    {
        if (!isset($this->permissions['is_admin'])) {
            return false;
        }
        return $this->permissions['is_admin'];
    }

    public function isClockedIn()
    {
        return $this->timeclockState['isClockedIn'] ?? null;
    }

    public function isLoggedIn()
    {
        if (!isset($this->authState['loggedIn'])) {
            return false;
        }
        return $this->authState['loggedIn'];
    }

    public function isSuperAdmin()
    {
        return PermissionLevels::isSuperAdmin($this->get('permissionId'));
    }

    public function logout()
    {
        $this->authState['loggedIn'] = 0;
        $this->session->logout();
    }

    public function getPermission($name)
    {
        return $this->permissions[$name];
    }

    public function mustResetPass()
    {
        return $this->session->pluck('passForcedReset');
    }
}
