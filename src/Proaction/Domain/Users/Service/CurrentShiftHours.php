<?php

namespace Proaction\Domain\Users\Service;

use Proaction\Domain\Timesheets\Model\Shift;
use Proaction\Domain\Timesheets\Service\CompressShifts;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Helpers\Misc;

class CurrentShiftHours
{
    public static function getByRange($employee_id, $range = [])
    {
        return (new static)->_getByRange($employee_id, $range);
    }

    public static function getCurrentWeek($employee_id)
    {
        return self::getByRange($employee_id, Misc::getPayrollDateRange());
    }

    public static function getCurrentDay($employee_id)
    {
        return self::getByRange(
            $employee_id,
            [
                date('Y-m-d 00:00:00'),
                date('Y-m-d 23:59:59'),
            ]
        );
    }

    public static function getCurrentMonth($employee_id)
    {
        return self::getByRange(
            $employee_id,
            [
                date('Y-m-01 00:00:00'),
                date('Y-m-d 23:59:59'),
            ]
        );
    }

    private function _getByRange($employee_id, $range = [])
    {
        $ids = $this->_getShiftIdsByEmployeeIdAndRange($employee_id, $range);
        if (is_null($ids)) {
            return [];
        }
        $shifts = new CompressShifts(Shift::whereIn('id', $ids)->get());
        return $shifts->getTotalAccumulative();
    }

    private function _getShiftIdsByEmployeeIdAndRange($employee_id, $range)
    {
        return Arr::flatten(Shift::where('employee_id', $employee_id)
            ->andWhereBetween('created_at', $range)
            ->get('id'));
    }
}
