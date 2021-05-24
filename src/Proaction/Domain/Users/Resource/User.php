<?php

namespace Proaction\Resource\User;

use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Employees\Model\EmployeePermissionView;
use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\Domain\Permissions\Model\AccessDetail;
use Proaction\Domain\Permissions\Model\PermissionLevels;
use Proaction\System\Resource\Helpers\Arr;

class User
{
    private static $instance;
    private $employeeId;
    private $userId;
    private $loggedIn;
    private $clockedIn;
    private $status;
    private $email;
    private $firstName;
    private $lastName;
    private $fullName;
    private $nickname;
    private $displayName;
    private $permissions;
    private $isSuperAdmin;

    private $passForcedReset = false;

    private $__GUEST = ['id' => 111111, 'firstName' => 'GUEST', 'lastName' => 'GUEST', 'nickname' => ''];

    private function __construct($baseUserSession = [])
    {

        if (empty($baseUserSession)) {

            $this->_createGuestUser();
        } else {

            if (!isset($baseUserSession['loggedIn'])) {

                $this->_createGuestUser();
            } else {

                $this->_setUser($baseUserSession);
            }
        }
    }


    private function _createGuestUser()
    {
        $permission_id = PermissionLevels::where('permission_label', 'Guest')->get('id');
        $permissions = AccessDetail::where('permission_id', $permission_id)
            ->leftJoin('permission_access_core', 'b', 'id', 'access_id')
            ->get(
                'a.value',
                'b.permission_short_name as name'
            );

        foreach ((array)$permissions as $perm) {
            $this->permissions[$perm['name']] = $perm['value'];
        }
        $this->_setIds();
        $this->_setLoggedIn(false);
        $this->_setStatus();
        $this->_setEmail();
        $this->_setNameValues();
        $this->clockedIn = false;
        $this->loggedIn = false;
    }

    public function isSuper() {
        return $this->isSuperAdmin;
    }

    public static function getInstance($baseUserSession = [])
    {
        if (!isset(self::$instance)) {
            self::$instance = new \Proaction\Resource\User\User($baseUserSession);
        }

        return self::$instance;
    }

    private function _setUser($baseUserSession)
    {
        $employee = EmployeeView::getSessionById($baseUserSession['userId']);
        // where('user_id', $baseUserSession['userId'])->limit(1)->get('id as employeeId', 'status', 'email', 'first_name as firstName', 'last_name as lastName', 'nickname', 'user_id as userId');
        if (isset($_GET['debug'])) {
            Arr::pre($employee);
        }
        $this->_setIds($employee);
        $this->_setLoggedIn($baseUserSession['loggedIn']);
        $this->_setStatus($employee);
        $this->_setEmail($employee);
        $this->_setNameValues($employee);
        $this->_setPermissions();
        $this->_setSuperAdminState($employee['employeeId']);
        $this->_getTimeclockStatus();
        $this->_setSecondaryUserValuesFromSession();
        $this->_logUserToSession();
    }

    private function _setSuperAdminState($id)
    {
        $permission_id = EmployeeView::find($id, ['permission_id']);
        $this->isSuperAdmin = PermissionLevels::find($permission_id, ['is_super']);
    }

    private function _setSecondaryUserValuesFromSession()
    {
        $this->passForcedReset = $_SESSION['user']['passForcedReset'];
    }

    private function _logUserToSession()
    {
        if ($this->loggedIn) {
            $_SESSION['user'] = array_merge(
                $_SESSION['user'],
                ['permissions' => $this->permissions],
                $this->getDisplayProps(),
                [
                    'passForcedReset' => $this->passForcedReset,
                    'sessionLoaded' => 1,
                ]
            );
        }
    }
    private function _getTimeclockStatus()
    {
        $this->clockedIn = $_SESSION['user']['clockedIn'] ?? Employee::isClockedIn($this->employeeId);
    }

    private function _setStatus($employee = null)
    {

        $this->status = is_null($employee) ? '' : $employee['status'];
    }

    private function _setIds($employee = null)
    {

        $this->employeeId = is_null($employee) ? $this->__GUEST['id'] : $employee['employeeId'];
        $this->userId = is_null($employee) ? $this->__GUEST['id'] : $employee['userId'];
    }

    private function _setLoggedIn($status)
    {
        $this->loggedIn = $status ?? false;
    }

    private function _setEmail($employee = null)
    {

        $this->email = is_null($employee) ? '' : $employee['email'];
    }

    private function _setNameValues($employee = null)
    {
        $target = is_null($employee) ? $this->__GUEST : $employee;
        $this->firstName = $target['firstName'];
        $this->lastName = $target['lastName'];
        $this->nickname = $target['nickname'];
        $this->fullName = $this->_setFullName($target);
        $this->displayName = $this->_setDisplayName($target);
    }

    private function _setFullName($employee)
    {
        extract($employee);
        if (is_null($nickname)) {
            return "$firstName $lastName";
        } else {
            return "$firstName \"$nickname\" $lastName";
        }
    }
    private function _setDisplayName($employee)
    {
        extract($employee);
        $lastInit = substr($lastName, 0, 1);
        return "$firstName $lastInit.";
    }

    private function _setPermissions()
    {
        $this->permissions = EmployeePermissionView::getByEmployeeId($this->employeeId);
    }

    public function get(string $prop)
    {
        $val = $this->{$prop};
        // if (!isset($val)) {
        //     die("Error: illegal property requested. User::get(prop) [$prop does not exist. Proaction\Resource\User");
        // }
        return $val;
    }

    public function isLoggedIn()
    {
        return $this->loggedIn;
    }

    public function isAdmin()
    {
        return $this->permissions['is_admin'];
    }

    public function isClockedIn()
    {
        return $this->clockedIn;
    }

    public function getDisplayProps()
    {
        return [
            'employeeId' => $this->employeeId,
            'userId' => $this->userId,
            'loggedIn' => $this->loggedIn,
            'firstName' => $this->firstName,
            'status' => $this->status,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'nickname' => $this->nickname,
            'fullName' => $this->fullName,
            'display' => $this->displayName,
            'isAdmin' => $this->isAdmin(),
            'clockedIn' => $this->clockedIn,
            'isSuperAdmin' => $this->isSuperAdmin,
        ];
    }

    public function getPermission(string $name)
    {
        return $this->permissions[$name];
    }

    private function _guestUser()
    {
        return [
            'loggedIn' => 0,
            'userId' => 111111,
        ];
    }
    public function setUserSessionValue($key, $value)
    {
        $this->{$key} = $value;
        $_SESSION['user'][$key] = $value;
    }

    public function clockout($empId)
    {
        if ($this->employeeId == $empId) {
            if ($this->clockedIn) {
                $this->clockedIn = false;
                $_SESSION['user']['clockedIn'] = false;
            }
        }
    }
}
