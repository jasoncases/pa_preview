<?php

namespace Proaction\Domain\Payroll\Model;

use Proaction\System\Database\CDB;
use Proaction\System\Model\ClientModel;

/**
 * CREATE OR REPLACE VIEW v_raw_shift_hours AS
 * SELECT
 * NOW(),
 * ROUND(SUM(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(a.created_at))/3600, 2) as hours,
 * c.department_id, c.department_label, DATE(a.created_at) as date
 * FROM ts_shifts AS a
 * LEFT JOIN ts_payroll_completed b ON b.shift_id=a.id
 * LEFT JOIN employee_view c ON c.id=a.employee_id
 * LEFT JOIN departments d ON d.id=c.department_id
 * GROUP BY date, c.department_id;
 */
class RawShiftHours extends ClientModel
{
    protected $table = 'v_raw_shift_hours';
    protected $isView = true;
    protected $view = 'CREATE OR REPLACE VIEW v_raw_shift_hours AS
                        SELECT
                        NOW(),
                        ROUND(SUM(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(a.created_at))/3600, 2) as hours,
                        c.department_id, c.department_label, DATE(a.created_at) as date
                        FROM ts_shifts AS a
                        LEFT JOIN ts_payroll_completed b ON b.shift_id=a.id
                        LEFT JOIN employee_view c ON c.id=a.employee_id
                        LEFT JOIN departments d ON d.id=c.department_id
                        GROUP BY date, c.department_id;';
    public static function unpaidHoursRemoved($date = null, $department_id = null)
    {
        $date = $date ?? date('Y-m-d');
        return (new static)->_unpaidHoursRemoved($date, $department_id);
    }

    private function _unpaidHoursRemoved($date, $department = null)
    {
        $x = $this->_sumUnpaidHours($date, $department);

        if (date('Y-m-d', strtotime($date)) != date('Y-m-d')) {
            return ClosedPayrollByDate::where('date', $date)
                ->where('department_id', $department)
                ->get('closed_hours');
        } else {
            return $this->_getShiftPayrollHoursByDepartment($date, $department);
        }
    }


    private function _getShiftPayrollHoursByDepartment($date, $department)
    {
        return $this->_getDepartmentHoursByDate($date, $department)
            - $this->_sumUnpaidHours($date, $department);
    }

    private function _sumUnpaidHours($date, $department)
    {
        $c = [];
        $time = time();
        $lunch_punches = $this->_getLunchPunches($date, $department);
        foreach ($lunch_punches as $lunch) {
            extract($lunch);
            if (is_null($c[$shift_id])) {
                $c[$shift_id] = $time - $unix_ts;
            } else {
                $c[$shift_id] = round(abs($time - $c[$shift_id] - $unix_ts) / 3600, 2);
            }
        }

        return array_sum($c);
    }

    private function _getLunchPunches($date, $department)
    {
        if (is_null($department)) {
            return Lunch::whereRaw('DATE(time_stamp) = ?', [$date])->get();
        } else {
            return Lunch::whereRaw('DATE(time_stamp) = ?', [$date])
                ->where('department_id', $department)
                ->get();
        }
    }

    private function _getDepartmentHoursByDate($date, $department)
    {
        if (is_null($department)) {
            return RawShiftHours::where('date', $date)->get(CDB::raw('SUM(hours) as hours'));
        } else {
            return RawShiftHours::where('date', $date)
                ->where('department_id', $department)
                ->get(CDB::raw('SUM(hours) as hours'));
        }
    }
}
