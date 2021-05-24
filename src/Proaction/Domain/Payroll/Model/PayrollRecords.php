<?php

namespace Proaction\Domain\Payroll\Model;

use Proaction\Domain\Clients\Model\GlobalSetting;
use Proaction\System\Database\CDB;
use Proaction\System\Model\ClientModel;
use Proaction\System\Resource\Helpers\Arr;

class PayrollRecords extends ClientModel
{
    //
    protected $table = 'v_payroll_records';
    protected $isView = true;
    protected $view = '';
    protected $pdoName = 'client';

    public $relations = [];

    public $attributes = [];

    public static function getEmployeesInRange($range)
    {
        $emps = self::whereRaw('DATE(shift_created_at) BETWEEN ? AND ?', $range)
            ->leftJoin('employee_view as b', 'b.id', 'v_payroll_records.employee_id')
            ->get([CDB::raw('DISTINCT(employee_id) as employee_id'), 'b.*']);
        return $emps->sortBy('id');
    }

    public static function getEmployeeTotalsByRange($employeeId, $from, $to = null)
    {
        if (is_array($from)) {
            $to = $from[1];
            $from = $from[0];
        }
        return self::where('employee_id', $employeeId)->whereRaw('DATE(shift_created_at) BETWEEN ? AND ?', [$from, $to])
            ->get(
                [
                    '*',
                    CDB::raw('ROUND(pay_rate * ' . GlobalSetting::get('overtime_multiplier') . ', 2) as overtimeRate'),
                    CDB::raw('ROUND((_reg/3600) * pay_rate, 2) as regularPaid'),
                    CDB::raw('ROUND((_ot/3600) * pay_rate * ' . GlobalSetting::get('overtime_multiplier') . ', 2) as overtimePaid'),
                ]
            );
    }

    public static function getPayrollRecords(string $from, string $to, array $options = [])
    {
        // \Proaction\Model\PayrollRecords::where('shift_created_at', '>', date('Y-m-d 00:00:00', strtotime($this->_searchedFrom)))->where('shift_created_at', '<', date('Y-m-d 23:59:59', strtotime($this->_searchedTo)))->oldest('employee_id')->get();
        $from = date('Y-m-d 00:00:00', strtotime($from));
        $to = date('Y-m-d 23:59:59', strtotime($to));
        return (new static)->_getPayrollRecords($from, $to, $options);
    }

    /**
     * Private Methods
     */

    private function _getPayrollRecords(string $from, string $to, array $options)
    {

        $options = current($options) == '' ? ['1', '1'] : $options;
        return self::where(
            'shift_created_at',
            '>',
            date('Y-m-d 00:00:00', strtotime($from))
        )
            ->where(
                'shift_created_at',
                '<',
                date('Y-m-d 23:59:59', strtotime($to))
            )
            ->where($options[0], $options[1])
            ->oldest('employee_id')
            ->get();
    }
}
