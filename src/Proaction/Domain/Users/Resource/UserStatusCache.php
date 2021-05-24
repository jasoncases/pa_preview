<?php

namespace Proaction\Domain\Users\Resource;

use Proaction\Domain\Employees\Model\EmployeePermissionView;
use Proaction\Domain\Timesheets\Model\EmployeeTimeclockStatus;
use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\System\Resource\Helpers\Arr;

class UserStatusCache
{

    private $cache = [];

    private $employees;
    private $lastGlobalHydrateTimestamp;

    private $maxAge = 600; // ttl in seconds=

    public function __construct($cache = [])
    {
        $this->lastGlobalHydrateTimestamp = $cache['lastGlobalHydrateTimestamp'] ?? null;
        $this->employees = $this->_initializeCache($cache['employees'] ?? null);
    }

    public function get($id = null)
    {
        if (is_null($id)) {
            return [
                'lastGlobalHydrateTimestamp' => $this->lastGlobalHydrateTimestamp,
                'employees' => $this->employees,
            ];
        } else if (is_numeric($id)) {
            return $this->employees[md5($id)];
        } else if ($id === 'lastGlobalHydrateTimestamp') {
            return $this->lastGlobalHydrateTimestamp;
        } else {
            throw new \Exception("Incorrect cache key requested: $id");
        }
    }

    private function _initializeCache($cache)
    {
        if (is_null($cache) || empty($cache) || $this->_cacheHasExpired()) {
            $this->_updateLastGlobaleHydrateTimestamp();
            return $this->_buildEmployees();
        }
        return $cache;
    }

    private function _cacheHasExpired()
    {
        return (time() - $this->lastGlobalHydrateTimestamp) > $this->maxAge;
    }

    private function _updateLastGlobaleHydrateTimestamp()
    {
        $this->lastGlobalHydrateTimestamp = time();
    }

    private function _buildEmployees()
    {
        $cache = [];
        $emps = EmployeeTimeclockStatus::getActiveStatus();
        foreach ($emps as $hash => $emp) {
            $emp['hours'] = (new UserHours($emp))->get();
            $emp['breakStatus'] = Timesheet::getBreakStatus($emp['employeeId']);
            $emp['permissions'] = EmployeePermissionView::getByEmployeeId($emp['employeeId']);
            $cache[$hash] = $emp;
        }
        return $cache;
    }

    public function updateUser($employee_id)
    {
        $hash = md5($employee_id);
        $this->employees[$hash] = $this->_updateSingleUserStatus($employee_id);
        return $this->get();
    }

    /**
     * TODO - This was written as a single update instance, but can be
     * TODO - converted to do the work in _buildEmployees as well.
     *
     * @param int $employeeId
     * @return array
     */
    private function _updateSingleUserStatus($employeeId)
    {
        $emp = EmployeeTimeclockStatus::getActiveStatus($employeeId);
        // >>> emp gets returned as a nested array with the hash as a
        // >>> top level id. That is needed for the original hydration,
        // >>> however, it causes a conflict when updating a user, so we
        // >>> need to set the hours and return the value, by using the
        // >>> hash as a reference
        $emp[md5($employeeId)]['hours'] = (new UserHours($emp[md5($employeeId)]))->get();
        $emp[md5($employeeId)]['breakStatus'] = Timesheet::getBreakStatus($employeeId);
        $emp[md5($employeeId)]['permissions'] = EmployeePermissionView::getByEmployeeId($employeeId);
        return $emp[md5($employeeId)];
    }

    public function logIn($employeeId)
    {
        return $this->updateLoggedInStatus($employeeId, true);
    }

    public function logOut($employeeId)
    {
        return $this->updateLoggedInStatus($employeeId, false);
    }

    public function updateLoggedInStatus($id, $bool)
    {
        $this->employees[md5($id)]['loggedIn'] = [
            'status' => $bool,
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s'),
        ];
        $this->_updateLastGlobaleHydrateTimestamp();
        return $this->get();
    }
}


/**
 * employees
 *      |--- {hash}
 *      |---|---hasThereBeenAStatusChange: bool
 *      |---|---clockedIn
 *      |---|---employeeId
 *      |---|---hours
 *      |---|---|---weeklyAccumulative
 *      |---|---|---monthlyAccumulative
 *      |---|---|---daily
 *      |---|---|---|---paid??
 *      |---|---|---|---clockInTimestamp??
 *      |---|---|---|---lunchInTimestamp??
 *      |---|---|---|---lunchDuration??
 *      |---|---status
 *      |---|---|---breakStatus
 *      |---|---|---..etc

 *
 */
