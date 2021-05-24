<?php

namespace Proaction\Service\Employee;

use Proaction\Domain\Employees\Model\EmployeeRate;
use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\System\Database\CDB;

/**
 * A repo object for the EmployeeController::getShowEmployment method
 */
class ShowEmployment
{
    public static function get($employee_id)
    {
        $viewObj = EmployeeView::find(
            $employee_id,
            [
                'department_id',
                'employment_status_id as status_id',
                'date_of_hire',
                'first_name',
                'last_name'
            ]
        );

        $rate = EmployeeRate::where('employee_id', $employee_id)
            ->latest('created_at')
            ->limit(1)
            ->first(CDB::raw('FORMAT(rate, 2) as rate'));
        $viewObj->rate = $rate->rate;
        return $viewObj;
    }
}
