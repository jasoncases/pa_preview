<?php

namespace Proaction\Domain\Payroll\Resource;

use Proaction\Domain\Timesheets\Model\Shift as ShiftModel;
use Proaction\System\Resource\Helpers\Arr;

class Shifts
{
    private $_renders = [];
    private $_shifts = [];

    public function __construct($from, $to, $employee_id)
    {

        $this->_shifts = $this->_getShifts($from, $to, $employee_id);
        $this->_init();
    }

    private function _init()
    {
        $this->_createShifts();
    }

    private function _createShifts()
    {
        foreach ($this->_shifts as $shift) {
            $this->_renders[] = $this->_createShift(new Shift($shift));
        }
    }

    private function _createShift(Shift $s)
    {
        return $s;
    }

    private function _getShifts($from, $to, $employee_id)
    {
        $shifts = ShiftModel::getEmployeeShiftsInRange($from, $to, $employee_id);
        return $shifts->isEmpty() ? [] : $shifts->sortBy('created_at');
    }

    public function render()
    {
        foreach ($this->_renders as $shift) {
            $shift->renderShiftContainer();
            $shift->render();
            $shift->renderCloseShiftContainer();
        }
    }
}
