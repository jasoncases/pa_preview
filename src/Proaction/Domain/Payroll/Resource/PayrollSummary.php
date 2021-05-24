<?php

namespace Proaction\Domain\Payroll\Resource;

use Proaction\Domain\Display\DisplayVersionTwo;

class PayrollSummary
{
    private $_data = [];
    public function __construct()
    {
    }

    public function render()
    {
        return view('Domain.Payroll.summary', $this->compressSummary());
    }

    public function addSummaryData($data)
    {
        $this->_data[] = $data;
    }

    private function compressSummary()
    {
        return [
            'sum_totalPay' => $this->_sumColumn('totalPay'),
            'sum_totalRegularHours' => $this->_sumColumn('totalRegularHours'),
            'sum_totalOvertimeHours' => $this->_sumColumn('totalOvertimeHours'),
            'sum_totalRegularPay' => $this->_sumColumn('totalRegularPay'),
            'sum_totalOvertimePay' => $this->_sumColumn('totalOvertimePay'),
            'sum_totalHoursWorked' => $this->_sumColumn('totalHoursWorked'),
        ];
    }

    private function _sumColumn($column)
    {
        return number_format(round(array_sum(array_column($this->_data, $column)), 2), 2, '.', '');
    }
}
