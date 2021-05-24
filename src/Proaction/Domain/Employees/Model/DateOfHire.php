<?php

namespace Proaction\Domain\Employees\Model;

use Proaction\System\Database\CDB;
use Proaction\System\Model\ClientModel;

class DateOfHire extends ClientModel
{

    protected $table = 'employee_date_of_hire';

    public static function day(int $employee_id)
    {
        return self::where('employee_id', $employee_id)
            ->get(CDB::raw('DAY(date) as dd'));
    }

    public static function month(int $employee_id)
    {
        return self::where('employee_id', $employee_id)
            ->get(CDB::raw('MONTH(date) as mm'));
    }

    public static function year(int $employee_id)
    {
        return self::where('employee_id', $employee_id)
            ->get(CDB::raw('YEAR(date) as yyyy'));
    }

    public static function get(int $employee_id)
    {
        return self::where('employee_id', $employee_id)
            ->get('date', CDB::raw('YEAR(date) as yyyy'), CDB::raw('MONTH(date) as mm'), CDB::raw('DAY(date) as dd'));
    }
}
