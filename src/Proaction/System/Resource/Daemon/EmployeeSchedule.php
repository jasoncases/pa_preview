<?php

namespace Proaction\Resource\Daemon\Schedule;

use Proaction\Model\Employee;
use Proaction\Model\EmployeeView;
use Proaction\Model\Meta\ClientGlobal;
use Proaction\Model\ScheduleShift;
use Proaction\Model\Shift;
use Proaction\Model\Timesheet;
use Proaction\System\Helpers\Arr;

class EmployeeSchedule
{
    private $id;
    private $shiftId;
    private $scheduled;
    private $clockedIn;
    private $scheduleStart;
    private $scheduleEnd;
    private $clockStart;
    private $clockEnd;
    private $duration;
    private $remaining;
    private $shiftCompleted;
    private $maxShiftLength;

    public function __construct($id)
    {
        $this->id = $id;
        $this->_load();
    }

    /**
     * Returns the array of employee schedule state values
     *
     * @return void
     */
    public function output()
    {
        settype($this->maxShiftLength, 'integer');
        return [
            'id'    => $this->id,
            'shiftId'   => $this->shiftId,
            'scheduled' => $this->scheduled,
            'clockedIn' => $this->clockedIn,
            'scheduleStart' => $this->scheduleStart,
            'scheduleEnd' => $this->scheduleEnd,
            'clockStart' => $this->clockStart,
            'clockEnd' => $this->clockEnd,
            'duration' => $this->duration,
            'remaining' => $this->remaining,
            'shiftCompleted' => $this->shiftCompleted,
            'maxShiftLength' => $this->maxShiftLength,
        ];
    }

    private function _load()
    {
        $this->_setMetaValues();
        $this->_setScheduleValues();
        $this->_setClockedInState();
    }

    private function _setMetaValues()
    {
        $this->maxShiftLength = ClientGlobal::get('unix_auto_clockout')['unix_auto_clockout'];
    }

    private function _setScheduleValues()
    {
        $shift = ScheduleShift::where('DATE(timestamp_start)', date('Y-m-d'))
            ->where('employee_id', $this->id)
            ->oldest()
            ->get();

        $this->scheduled = boolval($shift);
        if ($this->scheduled) {
            $this->scheduleStart = strtotime($shift['timestamp_start']);
            $this->scheduleEnd = strtotime($shift['timestamp_end']);
        }
    }

    private function _setClockedInState()
    {
        $this->clockedIn = Employee::isClockedIn($this->id);
        if ($this->clockedIn) {
            $this->_getShiftId();
            $this->_setClockTimeValues();
            $this->_setShiftCompleted();
            $this->_setDurationValues();
        }
    }

    private function _getShiftId()
    {
        $this->shiftId = Shift::where('employee_id', $this->id)->last()->get('id');
    }

    private function _setClockTimeValues()
    {
        $timesheetActions = Timesheet::where('shift_id', $this->shiftId)->get('unix_ts', 'activity_id');
        $this->clockStart = $this->_getUnixTimeByActionId($timesheetActions, 1);
        $this->clockEnd = $this->_getUnixTimeByActionId($timesheetActions, 0);
    }

    private function _getUnixTimeByActionId($actions, $actionId)
    {
        return current(array_filter($actions, function ($v, $k) use ($actionId) {
            return $v['activity_id'] == $actionId;
        }, ARRAY_FILTER_USE_BOTH))['unix_ts'] ?? null;
    }

    private function _setShiftCompleted()
    {
        $this->shiftCompleted = !is_null($this->clockEnd);
    }

    private function _setDurationValues()
    {
        if ($this->shiftCompleted) {
            $this->duration = $this->clockEnd - $this->clockStart;
            $this->remaining = 0;
        } else {
            $this->duration = time() - $this->clockStart;
            $this->remaining = $this->scheduleEnd ? $this->scheduleEnd - time() : null;
        }
    }
}
