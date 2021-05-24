<?php

namespace Proaction\Domain\Timesheets\Resource\Timeline;

use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Schedules\Model\ScheduleShift;

class Dow
{

    protected $schedule;
    protected $dotw;

    protected $shiftGroup;
    protected $shift_creation_date;
    protected $employee;

    protected $builder;

    protected $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

    public function __construct(Employee $employee, $date)
    {
        $this->employee = $employee;
        $this->shift_creation_date = date('Y-m-d', strtotime($date));
        $this->dotw = $this->days[date('w', strtotime($date))];
        $this->_getShiftGroup($date);
        $this->_getSchedule($date);
    }

    public function getMinAction()
    {
        return $this->shiftGroup->getMinAction();
    }
    public function getMaxAction()
    {
        return $this->shiftGroup->getMaxAction();
    }

    public function getOutput()
    {
        $shifts = $this->shiftGroup->getOutput();
        return [
            'dotw' => $this->dotw,
            'shift_creation_date' => $this->shift_creation_date,
            'shifts' => empty($shifts) ? null : $shifts,
            'schedule' => $this->schedule
        ];
    }

    private function _getSchedule($date)
    {
        $this->schedule = ScheduleShift::employeeBreakdownByDate($date, $this->employee->id);
    }

    private function _getShiftGroup($date)
    {
        $this->shiftGroup = new TimelineShifts($this->employee->id, $date);
    }
}
