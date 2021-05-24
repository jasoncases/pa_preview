<?php

namespace Proaction\Domain\Timesheets\Resource\States;

use Proaction\Domain\Timesheets\Model\Timesheet;

class ClockedIn extends TimesheetState
{
    private $breakCount;
    private $lunchCount;

    protected function _init()
    {
        $this->breakCount = $this->_getBreakCount();
        $this->lunchCount = $this->_getLunchCount();
    }

    private function _getBreakCount()
    {
        return Timesheet::getCurrentBreakCount($this->employee_id);
    }
    private function _getLunchCount()
    {
        return Timesheet::getCurrentLunchCount($this->employee_id);
    }

    protected function _getAllowedStates()
    {
        $c = [];

        $c[] = $this->_buildState('0');

        if ($this->breakCount < 2) {
            $c[] = $this->_buildState('3');
        }

        if ($this->lunchCount < 1) {
            $c[] = $this->_buildState('5');
        }

        return $c;
    }

}
