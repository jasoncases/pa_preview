<?php

namespace Proaction\Domain\Timesheets\Resource\States;

class ClockedOut extends TimesheetState
{
    protected function _getAllowedStates()
    {
        //
        return [
            $this->_buildState('1'),
        ];
    }
}
