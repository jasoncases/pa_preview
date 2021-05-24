<?php

namespace Proaction\Domain\Payroll\Service;

use Proaction\Domain\Payroll\Model\PayrollComplete;
use Proaction\Domain\Timesheets\Model\Shift;
use Proaction\Domain\Timesheets\Model\Timesheet;

class ShiftDetail
{
    public function __construct($id)
    {
        $this->id = $id;
        $this->_checkIfCompleteRecordExists();
    }

    public function getMeta(){
        return $this->_getShiftDetails();
    }

    public function getTimeline() {

    }

    private function _getStamps() {
        return Timesheet::getActionsByShiftId($this->id);
    }

    private function _getShiftDetails(){
        return Shift::where('a.id', $this->id)
        ->leftJoin('ts_payroll_completed', 'b', 'shift_id','a.id')
        ->leftJoin('employee_view', 'c', 'id', 'a.employee_id')
        ->get(
            'a.id',
            'c.first_name',
            'c.last_name',
            'c.fullDisplayName',
            'ROUND(b._reg/3600, 2) as reg',
            'ROUND(b._ot/3600, 2) as ot',
            'ROUND(b._clock/3600, 2) as clock',
            'ROUND(b._break/3600, 2) as break',
            'ROUND(b._lunch/3600, 2) as lunch',
            'ROUND(b._paid/3600, 2) as paid',
            'b._rate as rate'
        );
    }

    private function _checkIfCompleteRecordExists() {
        if (!PayrollComplete::isClosed($this->id)) {
            PayrollComplete::closeShiftByShiftId($this->id);
            PayrollComplete::updateClosedShift($this->id);
        }
    }

}
