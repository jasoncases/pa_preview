<?php

namespace Proaction\Domain\Timesheets\Resource;

use Proaction\Domain\Timesheets\Model\Shift;
use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\System\Resource\Helpers\Arr;

/**
 * Class searches for all employee timesheet data in range, both active
 * and inactive shifts and aggregates them to a employee detail and a
 * timesheet summary
 */
class Aggregate
{

    private $from, $to, $options;

    // initial container arrays for found shift information
    private $activeShifts = [];
    private $inactiveShifts = [];

    // active shifts are broken into `floating`, i.e., solo, no inactive
    // match, and `matched` where there is a matching inactive shift for
    // the employee_id value
    private $matchedActiveShifts = [];
    private $floatingActiveShifts = [];

    // combined is a staging array, where the found inactive shifts are
    // combined with any existing active shifts.
    // ! This data is in raw seconds
    private $combined = [];

    // these arrays are calculated down to hourly 2 decimal floats
    // both are returned through public methods, detail() & summary()
    private $output = [];
    private $aggregateOutput = [];

    // some constants
    private $overtimeInSeconds = 3600 * 40; // 40hrs
    private $overtimeMultiplier = 1.5;
    private $oneHourInSeconds = 3600;

    public function __construct($from, $to, $options = [])
    {
        $this->from = $from;
        $this->to = $to;
        $this->options = $options;
        $this->_init();
    }

    private function _init()
    {
        $this->_registerOptions();
        $this->_getActiveShifts();
        $this->_summarizeActiveShifts();
        $this->_getInactiveShifts();
        $this->_breakActiveShifts();
        $this->_combineShifts();
        $this->_aggregateData();
    }

    /**
     * Placeholder for now, but if we want to extend this to allow for
     * the changing of overtimeMultiplier or anything else, we can
     * process them here, so our math is correct down the road.
     *
     * @return void
     */
    private function _registerOptions()
    {
        if (isset($this->options['overtimeMultiplier'])) {
            $this->overtimeMultiplier = $this->options['overtimeMultiplier'];
        }
    }

    private function _breakActiveShifts()
    {
        $match = [];
        $unmatch = [];
        foreach ($this->activeShifts as $k => $active) {
            if ($this->_inactiveShiftByEmployeeIdExists($active['employee_id'])) {
                $match[] =  $active;
            } else {
                $unmatch[] = $active;
            }
        }

        $this->matchedActiveShifts = $match;
        $this->floatingActiveShifts = $unmatch;
    }

    private function _inactiveShiftByEmployeeIdExists($employee_id)
    {
        $employees = array_column($this->inactiveShifts, 'employee_id');
        return in_array($employee_id, $employees);
    }

    private function _summarizeActiveShifts()
    {
        $tmp = $this->activeShifts;
        foreach ($tmp as $k => $value) {
            extract($value);
            $week = Shift::getEmployeeHoursPerWeekByDate($this->to, $employee_id);
            // $week = Shift::getEmployeeHoursPerWeekByDate('2020-09-14', $employee_id);
            // if _reg >= 40, $tmp[$k]['_ot'] = $_paid;
            if ($week) {
                if ($week['_reg'] >= $this->overtimeInSeconds) {
                    $tmp[$k]['_ot'] = $_paid;
                    $tmp[$k]['_reg'] = $week['_reg'];
                } else if ($week['_reg'] + $_paid <= $this->overtimeInSeconds) {
                    //
                    $tmp[$k]['_reg'] += $_paid;
                    $tmp[$k]['_ot'] = 0;
                } else {
                    $diff = $this->overtimeInSeconds - $week['_reg'];
                    $tmp[$k]['_reg'] = $diff;
                    $tmp[$k]['_ot'] = $_paid - $diff;
                }
            } else {
                $tmp[$k]['_reg'] = $_paid;
                $tmp[$k]['_ot'] = 0;
            }

            $tmp[$k]['_regPaid'] = $_rate * $tmp[$k]['_reg'];
            $tmp[$k]['_otPaid'] = $_rate * $this->overtimeMultiplier * $tmp[$k]['_ot'];
        }


        $this->activeShifts = $tmp;
    }

    private function _getActiveShifts()
    {
        //
        throw new \Exception('Aggregate::_getActiveShifts needs to be updated to laravel');
        // $mock = new Mock('ts_shifts', 'client');
        // $activeBuilder = $mock->build();
        // $activeBuilder->where('active', 1)
        //     ->andWhereBetween('DATE(created_at)', [$this->from, $this->to])
        //     ->leftJoin('employee_view', 'b', 'id', 'employee_id')

        // if (isset($this->options['department_id'])) {
        //     $activeBuilder->where('b.department_id', $this->options['department_id']);
        // }

        // $activeShifts = Arr::flatten(
        //     $activeBuilder->get('a.id')
        // ) ?? [];

        // foreach ($activeShifts as $shift_id) {
        //     $shiftDetails = Timesheet::getTimesheetDetailByShiftId($shift_id);
        //     $calc = new Calc($shiftDetails);
        //     $this->activeShifts[]  = $calc->calc()->output();
        // }
    }

    private function _getInactiveShifts()
    {
        throw new \Exception('Aggregate::_getInactiveShifts needs to be updated to laravel');

        // $mock = new Mock('ts_payroll_completed', 'client');
        // $builder = $mock->build();
        // $builder->whereBetween('DATE(CREATED_AT)', [$this->from, $this->to])
        //     ->leftJoin('employee_view', 'b', 'id', 'employee_id')
        //     ->groupBy('a.employee_id')

        // if (isset($this->options['department_id'])) {
        //     $builder->where('b.department_id', $this->options['department_id']);
        // }

        // $otMultiplier = $this->overtimeMultiplier;

        // $results = $builder->get(
        //     'b.first_name',
        //     'b.last_name',
        //     'a.employee_id',
        //     'MAX(a._rate) as _rate',
        //     'SUM(a._clock) as _clock',
        //     'SUM(a._break) as _break',
        //     'SUM(a._lunch) as _lunch',
        //     'SUM(a._paid) as _paid',
        //     'SUM(a._reg) as _reg',
        //     'SUM(a._ot) as _ot',
        //     'SUM(a._reg * a._rate) as _regPaid',
        //     "SUM(a._ot * a._rate * " . $otMultiplier . " ) as _otPaid",
        // ) ?? [];

        // $this->inactiveShifts = $results;
    }

    private function _combineShifts()
    {
        $c = [];
        if (count($this->inactiveShifts)) {

            $inactive = $this->inactiveShifts;
            foreach ($inactive as $k => $inact) {
                $c[$k] = $inact;
                // combine active and inactive
                $active = $this->_getActiveRecordByEmployeeId($inact['employee_id']);

                if ($active) {
                    $c[$k]['_reg'] += $active['_reg'];
                    $c[$k]['_ot'] += $active['_ot'];
                    $c[$k]['_regPaid'] += $active['_regPaid'];
                    $c[$k]['_otPaid'] += $active['_otPaid'];
                }
            }
        }

        $this->combined = array_merge($c, $this->floatingActiveShifts);
    }

    /**
     * Since an employee can only have one active shift at a time, we
     * return the current() result of the array_filter()
     *
     * @param int $employee_id
     * @return array Stamp Record
     */
    private function _getActiveRecordByEmployeeId($employee_id)
    {
        return  current(array_filter($this->matchedActiveShifts, function ($v, $k) use ($employee_id) {
            return $v['employee_id'] == $employee_id;
        }, ARRAY_FILTER_USE_BOTH));
    }

    private function _aggregateData()
    {
        $this->output = $this->_convertToHourFormat();
        $this->aggregateOutput = $this->_sumAllData();
    }

    private function _convertToHourFormat()
    {
        $c = [];
        foreach ($this->combined as $k => $record) {
            $c[] = $this->_convertRecordToHourFormat($record);
        }
        return $c;
    }

    private function _convertRecordToHourFormat($record)
    {
        $columsToConvert = ['_clock', '_break', '_lunch', '_paid', '_reg', '_ot', '_regPaid', '_otPaid'];
        foreach ($record as $key => $value) {
            if (in_array($key, $columsToConvert)) {
                $record[$key] = number_format($value / $this->oneHourInSeconds, 2, '.', '');
            }
        }
        return $record;
    }

    private function _sumAllData()
    {
        $totalClockHours = array_sum(array_column($this->output, '_clock'));
        $totalBreakHours = array_sum(array_column($this->output, '_break'));
        $totalLunchHours = array_sum(array_column($this->output, '_lunch'));
        $totalPaidHours = array_sum(array_column($this->output, '_paid'));
        $totalRegularHours = array_sum(array_column($this->output, '_reg'));
        $totalOvertimeHours = array_sum(array_column($this->output, '_ot'));
        $totalRegularPaid = array_sum(array_column($this->output, '_regPaid'));
        $totalOvertimePaid = array_sum(array_column($this->output, '_otPaid'));
        $totalPay = $totalRegularPaid + $totalOvertimePaid;
        return compact(
            'totalClockHours',
            'totalBreakHours',
            'totalLunchHours',
            'totalPaidHours',
            'totalRegularHours',
            'totalOvertimeHours',
            'totalRegularPaid',
            'totalOvertimePaid',
            'totalPay',
        );
    }

    public function summary()
    {
        return $this->aggregateOutput;
    }

    public function detail()
    {
        return $this->output;
    }
    // active vs inactive shifts
    /**
     * get active shifts in date range
     * get inactive shifts in date range
     * combine
     * sum
     * output
     */
}
