<?php

namespace Proaction\System\Resource\Daemon;

use Proaction\App\Logger\Log;
use Proaction\System\Helpers\Arr;

/**
 * ClockAlert handles clockin/clockout alert issues not having to do
 * with tardiness for a shift, currently as of (5/22/20), it only
 * runs a force clockout process
 */
class ClockAlert extends AlertType
{
    protected $type = 'clock';

    protected function clockout()
    {

        if ($this->_clockoutEmployee()) {
            return $this->_getEmailAlert('forced_clockout')->send();
        } else {
            Log::warning(
                'Force clockout alert called, but clockout action failed.',
                [
                    'employee_is_clocked_in'
                    => \Proaction\Model\Employee::isClockedIn($this->employeeId)
                ]
            );
        }
    }

    private function _clockoutEmployee()
    {
        if (\Proaction\Model\Employee::isClockedIn($this->employeeId)) {
            $action = new \Proaction\Resource\Timesheet\TimesheetAction();
            return $action->daemonForceClockout($this->employeeId);
        }
        return false;
    }
}
