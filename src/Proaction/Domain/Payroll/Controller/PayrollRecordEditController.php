<?php

namespace Proaction\Domain\Payroll\Controller;

use Proaction\Domain\Payroll\ViewBuilders\PayrollRecordEdit;
use Proaction\System\Controller\BaseProactionController;
use Proaction\System\Views\GeneralView;

/**
 * Render the view for editing an employee's shift data.
 *
 * * route: /payroll_edit/{id} [GET]
 */
class PayrollRecordEditController extends BaseProactionController
{
    public function __invoke($id)
    {
        return view(
            'Domain.Payroll.edit_record',
            PayrollRecordEdit::add([], ['shift_id' => $id])
        );
    }
}
