<?php

namespace Proaction\Domain\Timesheets\Resource\States;

use Proaction\Domain\Worktypes\Model\Worktype;

class TimesheetState
{

    protected $employee_id;
    protected $state;

    public function __construct($employee_id, $state)
    {
        $this->employee_id = $employee_id;
        $this->state = $state;
        $this->_init();
    }

    protected function _init()
    {}

    public function get()
    {
        return $this->_getAllowedStates();
    }

    protected function _getAllowedStates()
    {

    }

    protected function _buildState($actionId)
    {
        return Worktype::where('actionId', $actionId)->get();
    }
}
