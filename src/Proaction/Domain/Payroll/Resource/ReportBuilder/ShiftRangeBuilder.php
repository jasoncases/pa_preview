<?php

namespace Proaction\Domain\Payroll\Resource\ReportBuilder;

use Proaction\Domain\Timesheets\Model\Shift;
use Proaction\Domain\Timesheets\Model\Timesheet;

class ShiftRangeBuilder
{

    private $from, $to, $employee_id;

    private $shifts;

    public function __construct($from, $to, $employee_id)
    {
        $this->from = $from;
        $this->to = $to;
        $this->employee_id = $employee_id;
        $this->_init();
    }

    public function get()
    {
        return $this->shifts;
    }

    private function _init()
    {
        $this->shifts = $this->_getShifts();
        $this->_getTimesheetActions();
    }

    private function _getTimesheetActions()
    {
        foreach ($this->shifts as $shift) {
            $this->_getTimesheetActionsByShift($shift);
        }
    }

    private function _getTimesheetActionsByShift(Shift $shift)
    {
        $stamps = Timesheet::getActionsByShiftId($shift->id);
        $actions = ShiftActionFormatter::go($stamps->sortBy('unix_ts'));
        $shift->employeeId = $this->employee_id;
        $shift->actions = $actions;
        $shift->totalHours = $actions->totalHours;
        $shift->nextDay = $actions->nextDay;
        $shift->date = $actions->date;
    }

    private function _getShifts()
    {
        $shifts = Shift::getEmployeeShiftsInRange($this->from, $this->to, $this->employee_id);
        return $shifts;
    }
}
