<?php

namespace Proaction\Domain\Timesheets\Resource;

use Proaction\Domain\Timesheets\Resource\States\Active;
use Proaction\Domain\Timesheets\Resource\States\ClockedIn;
use Proaction\Domain\Timesheets\Resource\States\ClockedOut;

class Allowable
{

    public static function get($employee_id, $state)
    {
        return (new static )->_allowedTimesheetActions($employee_id, $state);
    }

    private function _allowedTimesheetActions($employee_id, $state)
    {
        $this->employee_id = $employee_id;
        $this->currentState = $state;
        return $this->_createState($this->currentState)->get();
    }

    private function _createState($state)
    {
        if ($state == 0) {
            // return clocked out state
            return new ClockedOut($this->employee_id, $state);
        } else if ($state == 1 || $state < 0) {
            // return clocked in state
            return new ClockedIn($this->employee_id, $state);
        } else {
            // return active state
            return new Active($this->employee_id, $state);
        }
    }
}
