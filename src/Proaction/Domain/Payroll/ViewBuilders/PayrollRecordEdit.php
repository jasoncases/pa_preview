<?php

namespace Proaction\Domain\Payroll\ViewBuilders;

use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\Domain\Payroll\Resource\ReportBuilder\ShiftActionFormatter;
use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Views\Base\BaseViewDomain;

class PayrollRecordEdit extends BaseViewDomain
{
    protected function _getViewData()
    {
        $actions = Timesheet::getActionsByShiftId($this->localData['shift_id']);
        $shift = ShiftActionFormatter::go($actions);

        return [
            'shift' => $shift,
            'employee' => EmployeeView::find($shift->employeeId),
        ];
    }
}
