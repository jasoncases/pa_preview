<?php

namespace Proaction\Domain\Payroll\Service;

use Proaction\Domain\Payroll\Model\PayrollComplete;
use Proaction\Domain\Timesheets\Model\Shift;

class ShiftUpdater
{
    private $shift;

    public static function update($shift_id)
    {
        return (new static)->_update($shift_id);
    }

    private function _update($shift_id)
    {
        $this->shift = Shift::find($shift_id);
        PayrollComplete::updateShiftRecord(Shift::calculatedShiftRecord($this->shift));
        $this->_recalculateSummary();
    }

    private function _recalculateSummary()
    {
        return PayrollComplete::new__recalculate($this->shift);
    }
}
