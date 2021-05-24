<?php

namespace Proaction\Domain\Employees\Resource;

use Proaction\Domain\Employees\Model\DateOfHire;
use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Employees\Model\EmployeeDepartment;
use Proaction\Domain\Employees\Model\EmployeePermissions;
use Proaction\Domain\Employees\Model\EmployeeRate;
use Proaction\Domain\Employees\Model\EmploymentStatus;
use Proaction\Domain\Employees\Model\PendingEmployee;
use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\Domain\Users\Model\UserSetting;

class EmployeeMod
{

    private static $whitelist = ['user_id', 'first_name', 'last_name', 'nickname', 'email', 'phonetic', 'phone', 'date_of_hire', 'date_of_birth'];

    /**
     *
     * @param array $employeeArray
     * */
    public static function createNewEmployee(PendingEmployee $e)
    {
        return Employee::p_create(self::_cleanIncomingEmployeeData($e));
    }

    /**
     *
     *
     * */
    private static function _cleanIncomingEmployeeData(PendingEmployee $e)
    {
        $c = [];
        $data = $e->toArray();
        foreach ($data as $k => $v) {
            if (in_array($k, self::$whitelist)) {
                $c[$k] = $v;
            }
        }

        $c = self::_extendCleanse($c);
        return $c;
    }

    private static function _extendCleanse($arr)
    {
        $arr['phone'] = preg_replace("/[^0-9]/", "", $arr["phone"]);
        return $arr;
    }

    /**
     *
     *
     * */
    public static function insertEmployeeStatus($employee_id, $status_id)
    {
        return EmploymentStatus::p_create(compact('employee_id', 'status_id'));
    }
    /**
     *
     *
     * */
    public static function insertUserSettings($employee_id, $settingsObj)
    {
        return UserSetting::batch($employee_id, $settingsObj);
    }

    /**
     * Undocumented function
     *
     * @param [type] $employee_id
     * @param [type] $date
     * @return void
     */
    public static function insertEmployeeDateOfHire($employee_id, $date)
    {
        return DateOfHire::p_create(compact('employee_id', 'date'));
    }

    public static function insertEmployeeDepartment($employee_id, $department_id)
    {
        return EmployeeDepartment::p_create(compact('employee_id', 'department_id'));
    }

    public static function insertEmployeePermissions($employee_id, $permission_id)
    {
        return EmployeePermissions::p_create(compact('employee_id', 'permission_id'));
    }

    public static function insertEmployeeRate($employee_id, $rate)
    {
        $rate = number_format($rate, 2, '.', '');
        return EmployeeRate::p_create(compact('employee_id', 'rate'));
    }
}
