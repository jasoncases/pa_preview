<?php

namespace Proaction\Domain\Timesheets\Model;

use Proaction\Domain\Clients\Model\GlobalSetting;
use Proaction\Domain\Employees\Model\EmployeeRate;
use Proaction\Domain\Payroll\Model\PayrollComplete;
use Proaction\Domain\Payroll\Service\ShiftSegmentCalculator;
use Proaction\System\Database\CDB;
use Proaction\System\Model\ClientModel;
use Proaction\System\Resource\Comms\Comms;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Helpers\Misc;

use function Proaction\System\Helpers\Lib\getPayrollDateRange;

class Shift extends ClientModel
{
    //
    protected $table = 'ts_shifts';
    public $attributes = [];

    public static function getActiveByEmployeeId($empId)
    {
        return Shift::where('employee_id', $empId)->where('active', 1)->latest('id')->first();
    }

    public static function paidTimeInSeconds($shift_id)
    {
        return Timesheet::getCurrentShiftElapsed($shift_id)
            - Timesheet::getCurrentShiftLunchElapsed($shift_id);
    }

    public static function getLunchTimeInSeconds($shift_id)
    {
        return Timesheet::getLunchTimeInSecondsByShiftId($shift_id);
    }

    public static function getBreakTimeInSeconds($shift_id)
    {
        return Timesheet::getBreakTimeInSecondsByShiftId($shift_id);
    }

    public static function elapsedTimeInSeconds($shift_id)
    {
        return Timesheet::getCurrentShiftElapsed($shift_id);
    }

    /**
     * Store a new shift by employee id and return the Shift model
     *
     * Current payrate for employee is selected. This will require some
     * modification once roles are introduced
     *
     * @param int $employeeId
     * @return Shift
     */
    public static function storeNewByEmployeeId($employeeId)
    {
        // get the source of truth for employee pay rate
        $rate = EmployeeRate::getMostRecentPayRateByEmployeeId($employeeId);
        if (!$rate) {
            $rate = new EmployeeRate;
            $rate->rate = 0.00;
            // Comms::send('email', 'timesheet.MissingEmployeeRate', ['employeeId' => $employeeId]);
        }
        return self::p_create([
            'employee_id' => $employeeId,
            'pay_rate' => $rate->rate,
        ]);
    }



    public static function getIdsInDateRange($range, $employee_id = null)
    {
        return (new static)->_getIdsInDateRange($range, $employee_id);
    }

    private function _getIdsInDateRange($range, $employee_id = null)
    {
        return (isset($employee_id) && boolval($employee_id)) ?
            $this->_getIdsByDateRangeAndEmployeeId($range, $employee_id) :
            $this->_getIdsByDateRange($range);
    }

    private function _getIdsByDateRange($range)
    {
        return Shift::whereBetween('created_at', $range)
            ->where('active', 0)
            ->get('id')->pluck('id')->toArray();
    }

    private function _getIdsByDateRangeAndEmployeeId($range, $employee_id)
    {
        return Shift::whereBetween('created_at', $range)
            ->where('active', 0)
            ->where('employee_id', $employee_id)
            ->get('id')->pluck('id')->toArray();
    }

    /**
     * Reduce a shift to its component segments, i.e., _clock, _break,
     * _lunch, _paid
     *
     * @param array $shift
     * @return array
     */
    public static function calculatedShiftRecord($shift)
    {
        return (new static)->_calculateSegments($shift);
    }

    private function _calculateSegments($shift)
    {
        $timestamps = Timesheet::getActionsByShiftId($shift->id);
        $toInsert = [
            'shift_id'    => $shift->id,
            'employee_id' => $shift->employee_id,
            '_rate'       => $shift->pay_rate,
        ];
        return array_merge(
            $this->_calculateShiftSegments($timestamps),
            $toInsert
        );
    }

    private function _calculateShiftSegments($timestamps)
    {
        $calc = new ShiftSegmentCalculator($timestamps->toArray());
        return $calc->calc();
    }

    public static function getTotalHours($ids = [])
    {
    }

    public static function getPayrollWeekRangeByDate(string $date)
    {
    }

    public static function getPreviousEmployeeShift(int $shift_id)
    {
        $emp = self::find($shift_id, ['employee_id']);
        return self::where('id', '<', $shift_id)
            ->where('employee_id', $emp)
            ->latest('id')
            ->limit(1)
            ->get();
    }
    public static function getNextEmployeeShift(int $shift_id)
    {
        $currentShift = self::find($shift_id);
        if ($currentShift) {
            return self::where('id', '>', $shift_id)
                ->where('employee_id', $currentShift->employee_id)
                ->oldest('id')
                ->limit(1)
                ->first();
        }
    }

    public static function getLastEmployeeActivity($id)
    {
        return self::where('employee_id', $id)->where('active', 1)->latest('id')->limit(1)->first();
    }

    public static function getAllShiftsByDate(string $date)
    {
        return self::where('DATE(created_at)', $date)->get();
    }

    public static function getAllShiftsByDateAndEmployeeId(string $date, int $id)
    {
        return self::whereRaw('DATE(created_at)=?', [$date])->where('employee_id', $id)->get();
    }

    public static function getEmployeesWeeklyShiftsByDate($date = null, $employee_id)
    {
        $date = $date ?? date('Y-m-d');
        return self::whereBetween("created_at", Misc::getPayrollDateRange($date))->where('employee_id', $employee_id)->latest('id')->get();
    }

    public static function currentByEmployeeId($id)
    {
        return self::where('employee_id', $id)->where('active', 1)->latest('id')->limit(1)->first();
    }

    public static function getEmployeeHoursPerWeekByDate($date = null, $employee_id)
    {
        $shifts = self::getEmployeesWeeklyShiftsByDate($date, $employee_id);
        $shift_ids = array_column($shifts, 'id');
        return PayrollComplete::whereIn('shift_id', $shift_ids)
            ->where('employee_id', $employee_id)
            ->groupBy('employee_id')
            ->get(
                [
                    'employee_id',
                    CDB::raw('SUM( _paid ) as _paid'),
                    CDB::raw('SUM( _reg ) as _reg'),
                    CDB::raw('SUM( _ot ) as _ot'),
                ]
            );
    }


    public static function isOpen($shift_id)
    {
        return !Timesheet::where('shift_id', $shift_id)->where('activity_id', 0)->get();
    }
    public static function isEmpty($shift_id)
    {
        return Timesheet::where('shift_id', $shift_id)->get();
    }

    public static function getEmployeeShiftsInRange($from, $to, $employee_id)
    {
        return (new static)->_getEmployeeShiftsInRange($from, $to, $employee_id);
    }

    public static function checkForUnclosedShiftsByEmployeeId(int $employee_id)
    {
        return (new static)->_checkForUnclosedShiftsByEmployeeId($employee_id);
    }

    public static function close(int $id)
    {
        $active = 0;
        if (!isset($_GET['testing_payroll'])) {
            return self::p_update(compact('id', 'active'));
        } else {
            echo "<h2>Shift::close() called</h2>";
        }
    }

    public static function getActive(bool $verbose = false)
    {
        // $x = self::where('id', '>', 15)->get();
        // Arr::pre($x);
        if ($verbose) {
            return (new static)->_getShiftActionDetails(self::where('active', 1)->get());
        }

        return self::where('active', 1)->get();
    }

    /**
     * Private methods
     */

    private function _getShiftActionDetails($detailsArray)
    {
        $container = [];
        if (is_null($detailsArray)) {
            return $container;
        }
        foreach ($detailsArray as $shift) {
            $container[] = Timesheet::timeclockFormat($shift);
        }
        return $container;
    }

    private function _checkForUnclosedShiftsByEmployeeId(int $employee_id)
    {
        $shifts = Arr::flatten(self::where('employee_id', $employee_id)->get('id'));
        $c = [];
        foreach ($shifts as $id) {
            $pc = PayrollComplete::where('shift_id', $id)->get();
            if (!$pc && !self::isOpen($id)) {
                $c[] = compact('id', 'employee_id');
            }
        }
        return $c;
    }

    private function _getEmployeeShiftsInRange($from, $to, $employee_id)
    {
        $from = "$from 00:00:00";
        $to = "$to 23:59:59";
        return self::where('employee_id', $employee_id)
            ->whereRaw('created_at BETWEEN ? AND ?', [$from, $to])
            ->latest('id')
            ->get();
    }

    public static function getWeekStartOffset($date)
    {
        $mode = GlobalSetting::get('schedule_week_start');
        return $mode ? date('W', strtotime($date)) : strftime('%U', strtotime($date));
    }

    public static function getWeekStartByDate($date)
    {
        if (date('w', strtotime($date)) == GlobalSetting::get('schedule_week_start')) {
            return $date;
        }
        $target = GlobalSetting::get('schedule_week_start') ? 'Monday' : 'Sunday';
        return date('Y-m-d', strtotime("previous $target", strtotime($date)));
    }

    public static function getNextShift(Shift $s)
    {
        return self::whereRaw('id = (SELECT MIN(id) from ts_shifts WHERE id > ? AND employee_id = ?)', [$s->id, $s->employee_id])
            ->first();
    }

    public static function getPrevShift(Shift $s)
    {
        return self::whereRaw('id = (SELECT MAX(id) from ts_shifts WHERE id < ? AND employee_id = ?)', [$s->id, $s->employee_id])
            ->first();
    }

    public static function getNextShiftByShiftId(int $id)
    {
        return self::getNextShift(self::find($id));
    }

    public static function getPrevShiftByShiftId(int $id)
    {
        return self::getPrevShift(self::find($id));
    }
}
