<?php

namespace Proaction\Domain\Timesheets\Service;

use Exception\PayrollShiftNotFound;
use Proaction\Domain\Payroll\Model\PayrollComplete;
use Proaction\Domain\Timesheets\Model\Shift;

class ShiftCloser
{
    private $shift;

    public static function close($shiftId)
    {
        return (new static)->_close($shiftId);
    }

    private function _close($shiftId)
    {
        $this->shift = $this->_validateShift($shiftId);
        $this->_markShiftRecordInactive();
        return $this->_closeShift();
    }

    private function _closeShift() {
        return PayrollComplete::new__closeShift($this->shift);
    }

    private function _validateShift($shiftId) {
        $shift = Shift::find($shiftId);
        if (!$shift) {
            throw new PayrollShiftNotFound();
        }
        return $shift;
    }

    private function _markShiftRecordInactive()
    {
        $this->shift['active'] = 0;
        return Shift::p_update($this->shift);
    }
}
