<?php

namespace Proaction\Domain\Payroll\Model;

use Proaction\Domain\Payroll\Service\WeeklyPayrollCalculator;
use Proaction\Domain\Timesheets\Model\Shift;
use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\Domain\Timesheets\Service\ShiftCloser;
use Proaction\System\Database\CDB;
use Proaction\System\Model\ClientModel;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Helpers\Misc;


class PayrollComplete extends ClientModel
{
    //
    protected $table = 'ts_payroll_completed';
    protected $autoColumns = ['edited_by'];

    public static function getByRange($employee_id, $start, $end = null)
    {

        if (gettype($end) === 'integer') {
            $end = date('Y-m-d 23:59:59', $end);
        } else {
            $end = $end ?? date('Y-m-d 23:59:59');
        }

        $shifts = Shift::getEmployeeShiftsInRange(
            $start,
            $end,
            $employee_id
        );

        if ($shifts->isEmpty()) {
            return [
                'totalTimeClockedIn' => "0.00",
                'totalTimeOnBreak' => "0.00",
                'totalTimeAtLunch' => "0.00",
                'totalTimePaid' => "0.00",
            ];
        }

        $timeSegs = self::whereIn(
            'shift_id',
            $shifts->pluck('id')->toArray()
        )->first(
            [
                CDB::raw('ROUND(SUM(_clock)/3600, 2) as totalTimeClockedIn'),
                CDB::raw('ROUND(SUM(_break)/3600, 2) as totalTimeOnBreak'),
                CDB::raw('ROUND(SUM(_lunch)/3600, 2) as totalTimeAtLunch'),
                CDB::raw('ROUND(SUM(_paid)/3600, 2) as totalTimePaid'),
            ]
        );

        return $timeSegs->toArray();
    }

    public static function shiftExists($shiftId)
    {
        return self::where('shift_id', $shiftId)->get();
    }

    /**
     * Undocumented function
     *
     * @param Shift $shift
     * @return void
     */
    public static function new__recalculate($shift)
    {
        return (new static)->_new__recalculate($shift);
    }

    /**
     *
     */
    private function _new__recalculate($shift)
    {
        $range = Misc::getPayrollDateRange($shift->created_at);
        $shiftIds = Shift::getIdsInDateRange($range, $shift->employee_id);
        $records = $this->_getRecordsByShiftIds($shiftIds);
        return PayrollComplete::updateWeeklyAccumulatedRecords($records);
    }

    public static function new__closeShift($shift)
    {
        return (new static)->_new__closeShift($shift);
    }

    /**
     * Inserts a new PayrollComplete record, and calculates the payroll
     * time for all shifts within the payroll date range. There is a
     * validation method that will check the provided source of truth,
     * i.e., the shift ids by date range and employee id, against the
     * found records in the PayrollComplete table. If there are any
     * existing diffs, each diff'd id gets closed via ShiftCloser::close
     *
     * @param Shift $shift
     * @return void
     */
    private function _new__closeShift($shift)
    {
        self::insertNewRecord($shift);
        // directly close the provided shift
        Shift::close($shift->id);

        $range = Misc::getPayrollDateRange($shift->created_at);
        // This is a source of truth for shift ids. If it exists in this
        // array, it is a shift that MUST BE calculated

        $shiftIds = Shift::getIdsInDateRange($range, $shift->employee_id);
        $records = $this->_getRecordsByShiftIds($shiftIds);
        return PayrollComplete::updateWeeklyAccumulatedRecords($records);
    }

    /**
     * Inserts a new PayrollComplete row, without the additional math to
     * calculate the regular and overtime values. Since the reg and ot
     * rely on taking the week as a whole entity, we need to gather and
     * aggregate the totals after the shift is inserted
     *
     * @param array $shift
     * @return bool
     */
    public static function insertNewRecord($shift)
    {
        $shift = Shift::calculatedShiftRecord($shift);
        return self::p_create($shift);
    }

    /**
     * Incoming shape:
     *
     * shift_id
     * employee_id
     * _clock
     * _paid
     * _break
     * _lunch
     * _rate
     *
     * @param array $updatedShift
     * @return bool
     */
    public static function updateShiftRecord($updatedShift)
    {
        $updatedShift['id'] = PayrollComplete::pluckByShiftId($updatedShift['shift_id'])['id'];
        return PayrollComplete::p_update($updatedShift);
    }
    /**
     * Once the proper records are found, we process the calculation,
     * done by the WeeklyPayrollCalculator and then update each found
     * record. At this point, the records for the week should be correct
     *
     * @param Collection $recs
     * @return void
     */
    public static function updateWeeklyAccumulatedRecords($recs)
    {
        $weeklyCalc = new WeeklyPayrollCalculator($recs->toArray());

        foreach ($weeklyCalc->calc() as $rec) {
            self::p_update($rec);
        }
        return true;
    }

    /**
     * Return PayrollComplete records by provided array of shift ids.
     * The shift ids being sent to this method are a `source of truth`
     * and definitively exist within the database and therefore, the
     * PayrollComplete records corresponding to them SHOULD exist. If
     * they do not, the validation will insert and run again, by a
     * recursive call to the parent method
     *
     * @param array $shiftIds
     * @return array
     */
    private function _getRecordsByShiftIds($shiftIds)
    {
        return $this->_validatePayrollRecords(
            $shiftIds,
            PayrollComplete::byShiftIds($shiftIds)
        );
    }

    public static function byShiftIds($shiftIds)
    {
        return self::whereIn('shift_id', $shiftIds)->get(['id', 'shift_id', '_paid']);
    }

    public static function pluckByShiftId($shiftId)
    {
        return self::where('shift_id', $shiftId)->get();
    }

    /**
     * Cylce through provided records and shift ids. Diff and compare.
     * If any records are missing, close and calculate, then return the
     * new set, otherwise, return the originally supplied array
     *
     * @param array $shiftIds
     * @param Collection $records
     * @return array
     */
    private function _validatePayrollRecords($shiftIds, $records)
    {
        $foundShiftIds = $records->pluck('shift_id')->toArray();
        $diff = array_diff($shiftIds, $foundShiftIds);
        if (!empty($diff)) {
            foreach (array_values($diff) as $shift_id) {
                ShiftCloser::close($shift_id);
            }
            return $this->_getRecordsByShiftIds($shiftIds);
        }
        return $records;
    }

    /** =======================================================================
     *
     *                       Public Static Methods
     *
     * ====================================================================== */

    /**
     * Performs closing operation on an open shift. Takes shift and employee id,
     * Creates a record in ts_payroll_complete calculating all shift segments
     * as well as Reg/Ot hour totals, stored in raw second format
     *
     * @param integer $shift_id
     * @param integer $employee_id
     * @return void
     */
    public static function closeShift(int $shift_id, int $employee_id)
    {
        return (new static)->_close($shift_id, $employee_id);
    }
    public static function closeShiftByShiftId(int $shift_id)
    {
        $employee_id = Shift::where('id', $shift_id)->get('employee_id');
        return (new static)->_close($shift_id, $employee_id);
    }

    public static function updateClosedShift(int $shift_id)
    {
        return (new static)->_updateClosedShift($shift_id);
    }

    public static function isClosed($shift_id)
    {
        return self::where('shift_id', $shift_id)->get();
    }

    /**
     * Performed after closing a shift. It takes a date as an argument,
     * defaulting to current because it needs to pull all records for the
     * week that shift is in, in order to properly calculate Reg pay vs
     * OT pay
     *
     * @param integer $employee_id
     * @param mixed $date
     * @return void
     */
    public static function calculateHours(int $employee_id, $date = null)
    {
        return (new static)->_calculateHours($employee_id, $date);
    }

    /**
     * Performs the same action as `calculateHours()`, but uses shift_id
     * to specify a starting point
     *
     * @param integer $shift_id
     * @return void
     */
    public static function recalculateClosedShiftSummary(int $shift_id)
    {
        return (new static)->_calculateHoursByShiftId($shift_id);
    }

    /**
     * Rarely used method to run through an employees entire shifts and recalculate.
     * Useful when importing data and/or fixing errors
     *
     * @param integer $employee_id
     * @return void
     */
    public static function recalculateAllShiftsByEmployeeId(int $employee_id)
    {
        return (new static)->_recalculateAllShiftsByEmployeeId($employee_id);
    }

    public static function getAllUnclosedShiftsByEmployeeId(int $employee_id)
    {
        return (new static)->_getAllUnclosedShiftsByEmployeeId($employee_id);
    }

    public static function removeEmptyShifts()
    {
        return (new static)->_removeEmptyShifts();
    }

    public static function hoursSince(string $date, int $employee_id)
    {
        $nullSet = [
            '_paid' => '0.00',
            '_lunch' => '0.00',
            '_break' => '0.00',
            '_clock' => '0.00',
        ];

        $x = Shift::where('created_at', '>=', $date)
            ->where('employee_id', $employee_id)
            ->oldest('id')
            ->first(CDB::raw('MIN(id) as id'));

        if (!$x) {
            return $nullSet;
        }

        $totalPaid = PayrollComplete::where('employee_id', $employee_id)
            ->whereRaw('shift_id >= ?', [$x->id])
            ->get(
                [
                    CDB::raw('round(SUM(_paid)/3600, 2) as _paid'),
                    CDB::raw('round(SUM(_lunch)/3600, 2) as _lunch'),
                    CDB::raw('round(SUM(_break)/3600, 2) as _break'),
                    CDB::raw('round(SUM(_clock)/3600, 2) as _clock'),
                ]
            );

        return $totalPaid;
    }
    /** =======================================================================
     *
     *                         Private methods
     *
     * ====================================================================== */

    private function _removeEmptyShifts()
    {
        $all = self::all('id', 'shift_id');
        foreach ($all as $shift) {
            extract((array) $shift);
            if (!Shift::where('id', $shift_id)->get()) {
                self::destroy($id);
            }
        }
    }

    private function _getAllUnclosedShiftsByEmployeeId(int $employee_id)
    {
        $c = [];
        $shift_ids = Arr::flatten(Shift::where('employee_id', $employee_id)->get('id'));
        foreach ($shift_ids as $val) {
            if (!self::where('shift_id', $val)->get()) {
                if (!Shift::isOpen($val)) {
                    $c[] = $val;
                }
            }
        }
        return $c;
    }

    private function _updateClosedShift(int $shift_id)
    {
        $actions = Timesheet::where('shift_id', $shift_id)->get(['unix_ts', 'activity_id']);
        return is_null($actions) ? false : $this->_update($actions, $shift_id);
    }

    /**
     *
     */
    private function _update(array $actions, int $shift_id)
    {
        $id = PayrollRecords::where('shift_id', $shift_id)->get('id'); // get payroll record id
        self::p_update(array_merge($this->_getClosedShiftValues($actions, $shift_id), compact('id', 'shift_id')));
        return self::recalculateClosedShiftSummary($shift_id);
    }

    private function _recalculateAllShiftsByEmployeeId(int $employee_id)
    {
        // check for shifts that haven't been 'closed' yet
        $this->_closeAndCalculateUnclosedShifts($employee_id);
        $this->_batchRecalculateShiftSummariesByShiftId($this->_getAllShiftsByEmployeeId($employee_id));
    }

    private function _batchRecalculateShiftSummariesByShiftId(array $shiftIds): void
    {
        foreach ($shiftIds as $id) {
            self::updateClosedShift($id);
            self::recalculateClosedShiftSummary($id);
        }
    }

    private function _getAllShiftsByEmployeeId(int $employee_id): array
    {
        $records = self::where('employee_id', $employee_id)->get('shift_id');
        return $records->isEmpty() ? [] : $records->pluck('shift_id')->toArray();
    }

    private function _closeAndCalculateUnclosedShifts(int $employee_id)
    {
        $shifts = Shift::checkForUnclosedShiftsByEmployeeId($employee_id);
        foreach ($shifts as $v) {
            extract($v);
            self::closeShift($id, $employee_id);
            self::recalculateClosedShiftSummary($id);
        }
    }
    /**
     * @param integer $shift_id
     * @return void
     */
    private function _calculateHoursByShiftId(int $shift_id)
    {
        $shift = Shift::where('id', $shift_id)->get([CDB::raw('DATE(created_at) as date'), 'employee_id']);
        return $this->_calculateHours($shift->employee_id, $shift->date);
    }

    /**
     * This method acquires the shifts and data to perform the calculations
     *
     * @param integer $employee_id
     * @param [type] $date
     * @return void
     */
    private function _calculateHours(int $employee_id, $date = null)
    {

        $this->_emailLog('_calculateHours called()', $employee_id);
        // get shifts in the week via Shift model
        $shifts = $this->_getEmployeesWeeklyShifts($date, $employee_id);

        $this->_emailLog('shifts: ' . json_encode($shifts), $employee_id);

        // short circuit to returning false if there are no shifts
        if (is_null($shifts) || $shifts->isEmpty()) {
            // echo "[ date: $date ][ empid: $employee_id ]";
            $this->log('warning', 'No shifts found.');
            return false;
        }

        // get the payroll records for the employee where shift_ids are in the above shifts array
        $timeArr = self::whereIn('shift_id', $shifts->pluck('id')->toArray())
            ->where('employee_id', $employee_id)
            ->oldest('shift_id')
            ->get(['id', 'shift_id', '_paid']);

        $this->_emailLog('shifts: ' . json_encode($timeArr->toArray()), $employee_id);
        // return the next method
        return $this->_iterateAndCalculateAccumulativeHours($timeArr->toArray());
    }

    public function _getEmployeesWeeklyShifts($date, $employee_id)
    {
        $shifts = Shift::getEmployeesWeeklyShiftsByDate($date, $employee_id);

        // logically, this checks if the current dow is 0 and if shifts
        // came back null/empty, run it agian but go back a day. I am
        // not sure what this accompolishes.
        if (date("w") == 0 && (is_null($shifts) || $shifts->isEmpty())) {
            $shifts = Shift::getEmployeesWeeklyShiftsByDate(
                date('Y-m-d', strtotime('-1 day', strtotime($date))),
                $employee_id
            );
        }

        return $shifts;
    }


    private function _validateDate($date)
    {
    }
    /**
     * The main calculation method
     *
     * @param [type] $timeArr
     * @return void
     */
    private function _iterateAndCalculateAccumulativeHours(array $timeArr)
    {

        $this->_emailLog('iterate init()' . json_encode($timeArr), 14);
        // this is redundant, I believe the earlier call to Arr::retMulti() renders this pointless
        $timeArr = empty(array_count_values(array_column($timeArr, '_paid'))) ? [$timeArr] : $timeArr;

        // instantiate an accumulator
        $accum = 0;

        /**
         * Each iteration, the accumulator is compared against the paid hours for the shift
         * via the _getHours method. ['reg', 'ot'] is returned and merged into the timeArr
         * the shift_id and paid values are unset (precautionary)
         * and the accumulator is increased
         */
        foreach ($timeArr as $k => $v) {
            extract($v);
            $timeArr[$k] = array_merge($timeArr[$k], $this->_getHours($accum, $_paid));
            unset($timeArr[$k]['_paid']);
            unset($timeArr[$k]['shift_id']);
            $accum += $_paid;
            // update values w/ the reg and ot returned by _getHours
            self::p_update($timeArr[$k]);
        }

        $this->_emailLog('iterate completed', 14);

        return true;
    }

    /**
     * Given a threshold of 40 hours (*3600 seconds), decide if the hours are
     * regular or overtime
     *
     * @param int $elapsedSeconds
     * @param int $paid
     * @return array
     */
    private function _getHours($elapsedSeconds, $paid)
    {
        $otThreshold = 144000; // Need to pull this from Client Globals
        $_reg = 0; // instantiate
        $_ot = 0; // instantiate

        // if accumulated is over the threshold, all time is ot
        if ($elapsedSeconds > $otThreshold) {
            $_ot = $paid;
        } else {

            // if the accumulated + the paid is below ot, then all time is reg
            if ($elapsedSeconds + $paid < $otThreshold) {
                $_reg = $paid;
            } else {

                // if the accum + paid is greater than the threshold, get the overage.
                // overage is the ot while paid - overage is reg
                // ! this calculation should only happen once per iteration
                // ! all other states fall into the other two logic scenarios
                $overage = $elapsedSeconds + $paid - $otThreshold;
                $_reg = $paid - $overage;
                $_ot = $overage;
            }
        }

        // return the _reg and _ot values as an array for merging
        return compact('_reg', '_ot');
    }

    /**
     * Initiates the shift closing
     *
     * @param integer $shift_id
     * @param integer $employee_id
     * @return void
     */
    private function _close(int $shift_id, int $employee_id)
    {
        // update the shift model to change active to 0
        // gets all timestamp actions for a given shift id
        $actions = Timesheet::where('shift_id', $shift_id)->get(['unix_ts', 'activity_id']);
        $this->_closeShiftModel($shift_id);
        return $this->_insertClosedShift($actions, $shift_id, $employee_id);
    }

    /**
     * Set active to 0 in `ts_shifts`
     *
     * @param int $shift_id
     * @return bool
     */
    private function _closeShiftModel(int $shift_id)
    {
        if (!isset($_GET['testing_payroll'])) {
            return Shift::close($shift_id);
        } else {
            echo "<h2>Shift::close() called, shiftId: $shift_id </h2>";
        }
    }

    /**
     * Save a new payroll complete record
     *
     * @param [type] $actions
     * @param [type] $shift_id
     * @param [type] $employee_id
     * @return void
     */
    private function _insertClosedShift($actions, int $shift_id, int $employee_id)
    {
        if (!isset($_GET['testing_payroll'])) {
            // save the values, from the shift actions and merge w/ incoming args
            return self::p_create(array_merge($this->_getClosedShiftValues($actions->toArray(), $shift_id), compact('shift_id', 'employee_id')));
        } else {
            echo "<h2>PayrollComplete::_insertClosedShift() output";
            Arr::pre(array_merge($this->_getClosedShiftValues($actions->toArray(), $shift_id), compact('shift_id', 'employee_id')));
        }
    }

    /**
     * Break shift actions into segments for lengths and return
     * an array of accumulative values
     *
     * @param [type] $actions
     * @param [type] $shift_id
     * @return void
     */
    private function _getClosedShiftValues(array $actions, int $shift_id): array
    {
        $_rate = Shift::where('id', $shift_id)->first('pay_rate')->pay_rate;
        $_lunch = $this->_getShiftSegment('lunch', $actions);
        $_break = $this->_getShiftSegment('break', $actions);
        $_clock = $this->_getShiftSegment('shift', $actions);
        $_paid = $_clock - $_lunch;
        return compact('_rate', '_lunch', '_break', '_clock', '_paid');
    }

    /**
     * Switch to return proper values
     *
     * @param [type] $seg
     * @param [type] $actions
     * @return int
     */
    private function _getShiftSegment(string $seg, array $actions)
    {
        switch ($seg) {
            case 'lunch':
                return $this->_retSeg([5, -5], $actions);
            case 'break':
                return $this->_retSeg([3, -3], $actions);
            case 'shift':
                return $this->_retSeg([0, 1], $actions);
        }
    }

    /**
     * Filters the array for values in the ids sent, then does the math
     *
     * @param array $activityIds
     * @param array $actions
     * @return void
     */
    private function _retSeg(array $activityIds, array $actions): int
    {
        $tsActions = array_values(array_filter($actions, function ($v, $k) use ($activityIds) {
            return in_array($v['activity_id'], $activityIds);
        }, ARRAY_FILTER_USE_BOTH));
        return $this->_sumActions($tsActions);
    }

    /**
     * Loop through the resulting filtered actions and perform accumulative calc
     * while deducting elapsed time from pairs
     *
     * @param array $actions
     * @return int
     */
    private function _sumActions(array $actions): int
    {
        $this->_emailLog(json_encode($actions), 14);
        $x = 0;
        $accum = 0;
        while ($x < count($actions)) {
            $accum += $actions[$x + 1]['unix_ts'] - $actions[$x]['unix_ts'];
            $x += 2;
        }
        return $accum;
    }

    private function _emailLog($msg, $id)
    {
    }
}
