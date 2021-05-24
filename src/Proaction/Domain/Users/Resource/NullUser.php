<?php

namespace Proaction\Domain\Users\Resource;

use Proaction\Domain\Employees\Model\EmployeePermissionView;
use Proaction\Domain\Permissions\Model\PermissionLevels;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Session\UserSession;

class NullUser extends BaseUserProaction
{

    protected $type = 'guest';

    protected function _init()
    {
    }

    protected function _setPersonalData()
    {
        $this->personalData = [
            'id' => 11111111,
            'firstName' => 'GUEST',
            'lastName' => 'GUEST',
            'nickname' => '',
            'fullDisplayName' => 'GUEST',
            'email' => '',
            'permission_id' => PermissionLevels::getGuestId(),
        ];
    }

    protected function _setAuthState()
    {
        $this->authState = ['loggedIn' => 0];
    }

    protected function _loadPermissions()
    {
        $this->permissions = EmployeePermissionView::getByPermissionLevelId(
            PermissionLevels::getGuestId()
        );
    }

    protected function _setTimeclockState()
    {
        $this->timeclockState = ['clockedIn' => 0, 'status' => []];
    }
}
