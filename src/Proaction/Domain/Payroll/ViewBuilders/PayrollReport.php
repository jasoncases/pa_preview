<?php

namespace Proaction\Domain\Payroll\ViewBuilders;

use Illuminate\Database\Eloquent\Collection;
use Proaction\Domain\Employees\Model\Department;
use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\Domain\Payroll\Model\PayrollRecords;
use Proaction\Domain\Payroll\Resource\ReportBuilder\PayrollReportBuilder;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Helpers\Misc;
use Proaction\System\Views\Base\BaseViewDomain;

class PayrollReport extends BaseViewDomain
{

    protected function _getViewData()
    {
        $builder = new PayrollReportBuilder($this->localData['from'], $this->localData['to'], $this->localData['filterSearch'] ?? []);
        return [
            'to' => date('M d, Y', strtotime($this->localData['to'])),
            'from' => date('M d, Y', strtotime($this->localData['from'])),
            'employees' => EmployeeView::getActiveEmployees(),
            'dates' => $this->localData['fromJS'] . ' - ' . $this->localData['toJS'],
            'departments' => Department::all(),
            'employees' => $builder->get(),
            'summary' => $this->_summarizePayrollReport($builder->get())
        ];
    }

    private function _summarizePayrollReport(Collection $payroll)
    {
        $summary = new PayrollRecords;
        $summary->totalSummaryRegularHours = $this->_getSum($payroll, 'totalRegularHours');
        $summary->totalSummaryRegularPaid = $this->_getSum($payroll, 'totalRegularPaid');
        $summary->totalSummaryOvertimeHours = $this->_getSum($payroll, 'totalOvertimeHours');
        $summary->totalSummaryOvertimePaid = $this->_getSum($payroll, 'totalOvertimePaid');
        $summary->totalSummaryHours = $this->_getSum($payroll, 'totalHours');
        $summary->totalSummaryPaid = $this->_getSum($payroll, 'totalPaid');
        return $summary;
    }

    private function _getSum(Collection $payroll, $targetAttr)
    {
        $acc = 0;
        foreach ($payroll as $emp) {
            if (isset($emp->payroll)) {
                $acc += $emp->payroll->{$targetAttr};
            }
        }
        return Misc::money($acc);
    }
}
