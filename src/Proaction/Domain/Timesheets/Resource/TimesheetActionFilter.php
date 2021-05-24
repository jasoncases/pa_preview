<?php

namespace Proaction\Domain\Timesheets\Resource;

use Proaction\Domain\Clients\Model\GlobalSetting;
use Proaction\Domain\Employees\Model\EmployeeRate;
use Proaction\Domain\Payroll\Model\PayrollComplete;
use Proaction\Domain\Timesheets\Model\Shift;
use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Logger\Log;

/**
 * Filter all necessary Timesheet Action actions before performing the
 * actual punch and inserting the timestamp record
 */
class TimesheetActionFilter
{

    protected $_employeeId;
    protected $_lastAction;
    protected $_shiftId;

    protected $shift;

    protected $timestampActionMap = [
        '0'   => '_clockOut',
        '1'   => '_clockIn',
        '3'   => '_breakOut',
        '-3'  => '_breakIn',
        '5'   => '_lunchOut',
        '-5'  => '_lunchIn',
    ];

    /**
     * Straightforward punch of the time clock
     *
     * @param int $employee_id
     * @param int $activity_id - activity id maps to worktype_core table
     * @return void
     */
    public function punch($employee_id, $activity_id)
    {
        // set active employee id
        $this->_employeeId = $employee_id;
        // get last timesheet activity
        $this->_lastAction = $this->_lastActivity();
        $this->shift = $this->getShift();
        return $this->_filterAction($activity_id);
    }

    public function debugTest($employee_id, $activity_id)
    {
        $this->_employeeId = $employee_id;
        $this->_lastAction = $this->_lastActivity();
        $this->shift = $this->getShift();
        Arr::pre($this);
    }

    public function daemonForceClockout($employee_id)
    {
        $this->_employeeId = $employee_id;
        $this->_closeOpenStatePriorToClockout();
        return $this->_action()->forced($employee_id, 0, $this->shift->id, time());
    }

    private function _closeOpenStatePriorToClockout()
    {
        $this->shift = $this->getShift();
        $this->_lastAction = $this->_lastActivity();
        if ($this->_lastAction->activity_id > 1) {
            return $this->_filterAction(-$this->_lastAction->activity_id);
        }
    }

    public function forcedClockout($employee_id)
    {
        if (Shift::isOpen($this->shift->id)) {
            $start = Timesheet::getShiftStartUnix($this->shift->id);
            $maxTime = (int) GlobalSetting::get('timesheets_auto_clockout_after_hours');
            $unix_ts = $start + (3600 * $maxTime);
            return $this->_action()->forced($employee_id, 0, $this->shift->id, $unix_ts);
        }
    }

    private function _elapsedDuration()
    {
        return time() - Timesheet::getShiftStartUnix($this->shift->id);
    }

    private function _filterAction($activity_id)
    {
        if (array_key_exists($activity_id, $this->timestampActionMap)) {
            $action = $this->timestampActionMap[$activity_id];
            return $this->{$action}();
        } else {
            return $this->_custom($activity_id);
        }
    }

    private function getShift()
    {
        return Shift::getLastEmployeeActivity($this->_employeeId);
    }

    private function _lastActivity()
    {
        return  Timesheet::getLastEmployeeActivity($this->_employeeId);
    }

    private function _saveNewShift()
    {
        // create a shift record, creating an id which gets logged with
        // each timestamp action
        $this->shift = Shift::storeNewByEmployeeId($this->_employeeId);
        // log shift creation
        Log::info("New shift created.", ['employee_id' => $this->_employeeId, 'shift_id' => $this->shift->id]);
    }

    private function _action()
    {
        return new Action();
    }

    private function _clockIn()
    {
        $this->_clockInExceptions();
        $this->_saveNewShift();
        return $this->_action()->punch($this->_employeeId, 1, $this->shift->id);
    }

    private function _clockOut()
    {
        $this->_clockOutExceptions();

        return $this->_elapsedDuration() < 30 ? $this->_advanceClockout() : $this->_normalClockout();
    }

    private function _advanceClockout()
    {
        $flag = $this->_elapsedDuration() > GlobalSetting::get('clock_alert') ? 'unreviewed' : 'null';
        if ($this->_action()->punch($this->_employeeId, 0, $this->shift->id, 30, $flag)) {
            $this->_closeShift();
            $this->_calculateHours();
        } else {
            Log::warning('Error closing shift.', ['shift_id' => $this->shift->id]);
        }
    }

    private function _closeShift()
    {
        return PayrollComplete::new__closeShift($this->shift);
    }

    private function _calculateHours()
    {
        //
        // return PayrollComplete::calculateHours($this->_employeeId);
    }

    private function _normalClockout()
    {
        $flag = $this->_elapsedDuration() > GlobalSetting::get('clock_alert') ? 'unreviewed' : 'null';
        if ($this->_action()->punch($this->_employeeId, 0, $this->shift->id, $flag)) {
            $this->_closeShift();
            $this->_calculateHours();
        } else {
            Log::warning('Error closing shift.', ['shift_id' => $this->shift->id]);
        }
    }

    private function _segmentDuration()
    {
        return time() - $this->_lastAction['unix_ts'];
    }

    private function _lunchIn()
    {
        $this->_zeroStateException();
        $flag = $this->_getFlag("lunch_alert"); //$this->_segmentDuration() > GlobalSetting::get('lunch_alert') ? 'unreviewed' : 'null';
        return $this->_action()->punch($this->_employeeId, -5, $this->shift->id, $flag);
    }

    private function _lunchOut()
    {
        $this->_zeroStateException();
        return $this->_action()->punch($this->_employeeId, 5, $this->shift->id);
    }

    private function _breakIn()
    {
        $this->_zeroStateException();
        $flag = $this->_getFlag("break_alert"); // $this->_segmentDuration() > GlobalSetting GlobalSetting::get('break_alert') ? 'unreviewed' : 'null';
        return $this->_action()->punch($this->_employeeId, -3, $this->shift->id, $flag);
    }

    private function _getFlag($type)
    {
        $seg = $this->_segmentDuration();
        $alert = GlobalSetting::get($type);
        return $seg > $alert ? 'unreviewed' : 'null';
    }

    public function test($empid)
    {
        $this->_employeeId = $empid;
        $this->_lastAction = $this->_lastActivity();
        $this->shift = $this->getShift();

        Arr::pre($this);
    }
    private function _breakOut()
    {
        $this->_zeroStateException();
        return $this->_action()->punch($this->_employeeId, 3, $this->shift->id);
    }

    private function _custom($activity_id)
    {
        $this->_zeroStateException();
        return $this->_action()->punch($this->_employeeId, $activity_id, $this->shift->id);
    }

    private function _clockInExceptions()
    {
        if ($this->_lastAction->activity_id != 0) {
            throw new \Exception\IllegalWorktypeException('Invalid worktype selected. Clock in not allowed from current state.');
        }
        if ($this->_lastAction->unix_ts > time()) {
            throw new \Exception\TimesheetOverlapException();
        }
    }

    private function _clockOutExceptions()
    {
        $this->_zeroStateException();
    }

    private function _zeroStateException()
    {
        if ($this->_lastAction->activity_id == 0) {
            throw new \Exception('Invalid worktype selected. Clock out not allowed.');
        }
    }

    public function debug()
    {
        echo '<pre>';
        $this->_employeeId = 13;
        print_r($this);
        $seg = $this->_segmentDuration();
        echo "[ segment duration: $seg ]";
    }
}
