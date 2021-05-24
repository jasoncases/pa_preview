<?php

namespace Proaction\Domain\Users\Resource;

use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Employees\Model\EmployeePermissionView;
use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\System\Resource\Helpers\Arr;

class EmployeeUser extends BaseUserProaction
{

    protected $type = 'employee';

    protected function _init()
    {
    }

    public function getTimeclockStatusId()
    {
        if (!isset($this->timeclockState['status'])) {
            return 0;
        }

        // if activity_id is not set, return 0 for clocked out
        return $this->timeclockState['status']['activity_id'] ?? 0;
    }

    protected function _setPersonalData()
    {
        $this->identity = EmployeeView::getByUserId($this->userId);
        $this->personalData = $this->identity->attributes;
    }

    protected function _setAuthState()
    {
        $this->authState = ['loggedIn' => $this->session->pluck('loggedIn')];
    }

    protected function _setTimeclockState()
    {
        $this->timeclockState = [
            'isClockedIn' => Employee::isClockedIn($this->getEmployeeId()),
            'status' => Employee::getTimeclockStatus($this->getEmployeeId()),
        ];
    }

    protected function _loadPermissions()
    {
        $this->permissions = EmployeePermissionView::getByPermissionLevelId($this->get('permissionId'));
    }
}
