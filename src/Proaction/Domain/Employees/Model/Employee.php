<?php

namespace Proaction\Domain\Employees\Model;

use Proaction\Domain\Attendace\Model\Absence;
use Proaction\Domain\Clients\Model\GlobalSetting;
use Proaction\Domain\Payroll\Model\PayrollComplete;
use Proaction\Domain\Permissions\Model\PermissionLevels;
use Proaction\Domain\Timesheets\Model\EmployeeTimeclockStatus;
use Proaction\Domain\Timesheets\Model\Shift;
use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\Domain\Users\Model\Pin;
use Proaction\Domain\Worktypes\Model\Worktype;
use Proaction\System\Database\CDB;
use Proaction\System\Model\ClientModel;
use Proaction\System\Resource\Helpers\Arr;

class Employee extends ClientModel
{
    //
    protected $table = 'employees';
    protected $autoColumns = ['edited_by', 'author'];

    public static function getTimeclockStatus($employee_id)
    {
        $status = EmployeeTimeclockStatus::where('employee_id', $employee_id)->first();
        if (!$status) {
            return;
        }
        return $status->attributes;
    }

    public static function emailIsUnique($email)
    {
        return !boolval(self::where('email', $email)->where('status', 1)->first());
    }

    public static function phoneIsUnique($phone)
    {
        return !boolval(self::where('phone', $phone)->where('status', 1)->first());
    }

    public static function pinIsUnique($pin)
    {
        return Pin::isUnique($pin);
    }

    public static function email($ids = null)
    {
        if (is_null($ids)) {
            return self::allActiveEmail();
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        return Arr::flatten(self::whereIn('id', $ids)->get('email'));
    }

    public static function phonetic($ids)
    {
        if (empty($ids)) {
            return [];
        }
        if (is_null($ids)) {
            $emps = self::getActiveEmployees(['nickname', 'phonetic', 'first_name']);
            $c = [];
            foreach ($emps as $emp) {
                if (!is_null($emp['phonetic'])) {
                    $c[] = $emp['phonetic'];
                    continue;
                }
                if (!is_null($emp['nickname'])) {
                    $c[] = $emp['nickname'];
                    continue;
                }
                $c[] = $emp['first_name'];
            }
            return $c;
        }
        $emps = self::whereIn('id', $ids)->get(['nickname', 'phonetic', 'first_name']);
        $c = [];
        foreach ($emps as $emp) {
            if (!is_null($emp['phonetic'])) {
                $c[] = $emp['phonetic'];
                continue;
            }
            if (!is_null($emp['nickname'])) {
                $c[] = $emp['nickname'];
                continue;
            }
            $c[] = $emp['first_name'];
        }
        return $c;
    }

    public static function names($ids = null)
    {
        if (empty($ids)) {
            return [];
        }
        if (is_null($ids)) {
            return Arr::flatten(self::getActiveEmployees(['nickname']));
        }
        return Arr::flatten(self::whereIn('id', $ids)->get('nickname'));
    }

    private static function allActiveEmail()
    {
        return  Arr::flatten(Employee::getActiveEmployees(['email']));
    }


    public static function dailyTimeclockSummary(int $id)
    {
        return (new static)->_dailyTimeclockSummary($id);
    }

    public function employeeRole()
    {
        return $this->hasManyThrough('\Model\EmployeeRole', '\Model\Role');
    }

    public static function getCurrentTimeclock(int $id)
    {
        if (Employee::isClockedIn($id)) {
            return Timesheet::getCurrentShiftLength($id);
        } else {
            $shifts = Shift::getAllShiftsByDate(date('Y-m-d'));
            if ($shifts->isEmpyt()) {
                return 0;
            }
            $paid = PayrollComplete::whereIn('shift_id', $shifts->pluck('id')->toArray())->get('_paid');
            return array_sum($paid->pluck('_paid')->toArray());
        }
    }

    public static function getCurrentShiftId(int $id)
    {
        $shift = Shift::where('employee_id', $id)->where('active', 1)->latest('id')->first('id');
        return $shift->id ?? null;
    }

    public static function getActiveIds()
    {
        return Arr::flatten(Employee::getActiveEmployees(['id']));
    }

    public static function getActiveEmployees($columns = ['*'])
    {
        return Employee::where('status', 1)->get($columns);
    }

    public static function isClockedIn($id)
    {
        if (is_null($id)) {
            return;
        }
        $activity = Timesheet::where('ts_timesheet.employee_id', $id)
            ->where('ts_shifts.active', 1)
            ->leftJoin('ts_shifts', 'ts_shifts.id', '=', 'ts_timesheet.shift_id')
            ->latest('ts_timesheet.id')
            ->limit(1)
            ->first('ts_timesheet.activity_id');

        if (!$activity) {
            return;
        } else {
            return $activity->activity_id != 0;
        }
    }

    public static function getAttendance(int $id = null)
    {
        return (new static)->_getAttendance($id);
    }

    public static function getEmployeeAttendance(int $id)
    {
        return Absence::getAbsencesByEmployeeId($id);
    }

    public static function getTrackingDateRange($mode, $id)
    {
        return (new static)->_getTrackingDateRange($mode, $id);
    }

    public static function getAllAttendance()
    {
        $c = EmployeeView::getActiveEmployees(['id', 'first_name', 'last_name']);
        foreach ($c as $k => $v) {
            $c[$k]['absences'][] = Employee::getEmployeeAttendance($v['id']);
        }
        return $c;
    }

    public static function getAttendanceOrigination(int $employee_id, int $yearBack = null)
    {
        return (new static)->_getAttendanceOrigination($employee_id, $yearBack);
    }

    public static function getEarnedPTO(int $id)
    {
        // return Pto::getEarnedPto($id);
    }

    public static function getAdmin()
    {
        $adminLevels = PermissionLevels::getAdministratorLevelIds();
        $empIds = EmployeePermissions::whereIn('permission_id', $adminLevels)
            ->get('employee_id');
        return self::whereIn('id', $empIds->pluck('employee_id')->toArray())
            ->where('status', 1)
            ->get();
    }
    // ! Private methods ===============================================
    // ! Private methods ===============================================

    private function _getAttendanceOrigination(int $id, int $yearBack = null)
    {
        return $this->_filterAbsenceTrackingMode(
            GlobalSetting::get('absence_tracking_mode'),
            $id
        );
    }

    private function _getAttendance(int $id = null)
    {
        if (!is_null($id)) {
            return $this->_formatAttendance(Employee::getEmployeeAttendance($id));
        }
        return $this->_formatAttendance(Employee::getAllAttendance());
    }

    private function _formatAttendance(array $attendance)
    {
        Arr::pre($attendance);
    }

    private function _getTrackingDateRange($mode, $id)
    {
        switch ($mode) {
            case 0:
                return date('Y-01-01');
            case 1:
                return $this->_getCurrentEmployeeHireDate($id);
        }
    }

    private function _getCurrentEmployeeHireDate(int $id)
    {
        $dateOfHire = Employee::where('id', $id)->get('date_of_hire');
        $exp = explode('-', $dateOfHire);
        array_shift($exp);
        $x = implode('-', $exp);
        return date("Y-$x");
    }

    /**
     * Placed the return in a filter/switch method to allow for future
     * adjustments
     *
     * ! - side-note: this made testing easier as well, since I can
     * ! - easily just swap the switch statements, just need to ensure
     * ! - they are set correctly when done testing
     *
     * absense_tracking_mode: 0 = calendar year, 1 = date of employment
     *
     * @param int $mode
     * @param int $employee_id
     */
    private function _filterAbsenceTrackingMode(int $mode, int $employee_id)
    {
        switch ($mode) {
            case 0:
                // 0 - calendar year
                return date('Y-01-01');
            case 1:
                // 1 - date of employment
                return $this->_getEmployeeHireDate($employee_id);
            default:
                // 0 - calendar year
                return date('Y-01-01');
        }
    }

    private function _getEmployeeHireDate(int $id)
    {
        $dateOfHire = DateOfHire::where('employee_id', $id)
            ->get(CDB::raw('CONCAT(MONTH(date), "-", DAY(date)) as date'));
        return date("Y-" . $dateOfHire->date);
    }

    /**
     * Returns current activity_id and duration of current break state
     * for employee_id provided
     *
     * @param integer $id
     * @return array/null
     */
    public static function isOnBreak(int $id)
    {
        $onBreakActivityIds = [3, 5];
        $allLastActions = Timesheet::where('employee_id', $id)
            ->where('DATE(time_stamp)', date('Y-m-d'))
            ->latest('id')
            ->limit(1)
            ->first(['activity_id', CDB::raw('(UNIX_TIMESTAMP() - unix_ts) as duration')]);

        return in_array($allLastActions->activity_id, $onBreakActivityIds) ?
            $allLastActions : null;
    }

    public static function isOnLunch($id)
    {
        $status = EmployeeTimeclockStatus::where('employee_id', $id)->first('activity_id', 'time_stamp');

        if ($status) {
            if ($status->activity_id ===  Worktype::lunchId()) {
                return strtotime($status->time_stamp);
            }
        }
        return  null;
    }

    private static function _dailyTimeclockSummary($id)
    {
        $shift = Shift::where('employee_id', $id)->last()->get();
        Arr::pre(Timesheet::timeclockFormat($shift));
    }
};
