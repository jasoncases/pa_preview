<?php

namespace Proaction\Domain\Payroll\Model;

use Proaction\System\Model\ClientModel;

class ClosedPayrollByDate extends ClientModel
{
    protected $table = 'v_closed_payroll_hours_by_date';
    protected $isView = true;
    protected $view = 'CREATE OR REPLACE VIEW v_closed_payroll_hours_by_date AS
                        SELECT
                        ROUND(SUM(b._paid)/3600, 2) as closed_hours,
                        c.department_id,
                        c.department_label
                        FROM ts_shifts AS a
                        LEFT JOIN ts_payroll_completed b ON b.shift_id=a.id
                        LEFT JOIN employee_view c ON c.id=a.employee_id
                        LEFT JOIN departments d ON d.id=c.department_id
                        GROUP BY c.department_id;';
}
