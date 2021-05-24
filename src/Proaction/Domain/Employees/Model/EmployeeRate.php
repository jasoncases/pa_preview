<?php

namespace Proaction\Domain\Employees\Model;

use Proaction\System\Model\ClientModel;

class EmployeeRate extends ClientModel
{
    //
    protected $table = 'employee_rates';
    protected $autoColumns = ['author', 'edited_by'];

    public static function getMostRecentPayRateByEmployeeId($employee_id)
    {
        return self::where('employee_id', $employee_id)->latest('id')->limit(1)->first();
    }
}
