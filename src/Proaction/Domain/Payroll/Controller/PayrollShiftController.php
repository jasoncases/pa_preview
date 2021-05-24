<?php

namespace Proaction\Domain\Payroll\Controller;

use Proaction\Domain\Payroll\ViewBuilders\PayrollShifts;
use Proaction\System\Controller\BaseProactionController;

class PayrollShiftController extends BaseProactionController
{
    public function __invoke($employee_id, $from, $to)
    {
        return view(
            'Domain.Payroll.shifts_range',
            PayrollShifts::add([], compact('employee_id', 'from', 'to'))
        );
    }
}
