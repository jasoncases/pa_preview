<?php

namespace Proaction\Domain\Timesheets\Resource\States;

class Active extends TimesheetState
{

    private $inverseState;

    protected function _getAllowedStates()
    {
        return [
            $this->_buildState(-$this->state),
        ];
    }
}
