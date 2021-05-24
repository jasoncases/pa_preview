<?php

namespace Proaction\Domain\Employees\Service;

use Proaction\Domain\Employees\Model\PendingEmployee;
use Proaction\Domain\Employees\Resource\EmployeeMod;
use Proaction\System\Resource\Helpers\Arr;

class CommitSecondaryProps
{
    private $settingsKeys = ['allow_remote_timesheet_action'];

    public function __construct($employee_id, $userObj)
    {
        $this->_process($employee_id, $userObj);
    }

    private function _process($employee_id, PendingEmployee $e)
    {
        $c = [];
        $c['settings'] = $this->_extractSettings($e);
        EmployeeMod::insertEmployeePermissions($employee_id, $e->permission_id);
        EmployeeMod::insertEmployeeDepartment($employee_id, $e->department_id);
        EmployeeMod::insertEmployeeStatus($employee_id, $e->status_id);
        EmployeeMod::insertEmployeeRate($employee_id, $e->rate);
        EmployeeMod::insertUserSettings($employee_id, $c['settings']);
    }

    private function _extractSettings($userObj)
    {
        $c = [];
        $data = $userObj->toArray();
        foreach ($data as $k => $v) {
            if (in_array($k, $this->settingsKeys)) {
                $c[$k] = $v;
            }
        }
        return $c;
    }
}
