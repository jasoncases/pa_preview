<?php

namespace Proaction\Domain\Employees\Model;;

use Proaction\System\Model\ClientModel;


class EmployeeDepartment extends ClientModel
{
    protected $table = 'employee_departments';
    protected $autoColumns = ['edited_by'];

    public static function byEmployeeId(int $id)
    {
        return self::where('employee_id', $id)->get('department_id');
    }
}
