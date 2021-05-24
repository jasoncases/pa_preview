<?php

namespace Proaction\Domain\Timesheets\Resource;

use Proaction\Domain\Clients\Model\GlobalSetting;
use Proaction\Domain\Payroll\Model\PayrollRecords;
use Proaction\Domain\Payroll\Resource\ReportBuilder\PayrollReportBuilder;
use Proaction\Domain\Timesheets\Model\Shift;
use Proaction\System\Resource\Helpers\Misc;

class ActiveShiftBuilder
{

    protected $employeeId;
    protected $shift;
    protected $payRangeRecords;

    protected $overtimeThreshold = 40;

    public function __construct($employeeId)
    {
        $this->employeeId = $employeeId;
        $this->shift = Shift::getActiveByEmployeeId($employeeId);
    }

    public static function get($employeeId)
    {
        return (new ActiveShiftBuilder($employeeId))->_get();
    }

    private function _get()
    {
        if (!$this->shift) {
            return null;
        }
        $this->_getCurrentPayrollRangeRecords();
        return $this->_buildActivePayrollRecord();
    }

    private function _getCurrentPayrollRangeRecords()
    {
        $this->payRangeRecords = $this->_buildPayrollRangeForEmployee();
    }

    private function _buildPayrollRangeForEmployee()
    {
        $range = Misc::getPayrollDateRange();
        $builder = new PayrollReportBuilder($range[0], $range[1], ['singleEmployeeToggle' => true, 'employeeId' => $this->employeeId]);
        $record = $builder->get();
        return $record->isEmpty() ? null : $record->shift()->payroll;
    }

    private function _buildActivePayrollRecord()
    {
        $pr = new PayrollRecords;
        $this->_buildShiftSegments($pr);
        $this->_buildPayrollRecordSegments($pr);
        return $pr;
    }

    private function _buildShiftSegments(PayrollRecords $pr)
    {
        $pr->_clock = Shift::elapsedTimeInSeconds($this->shift->id);
        $pr->_break = Shift::getBreakTimeInSeconds($this->shift->id);
        $pr->_lunch = Shift::getLunchTimeInSeconds($this->shift->id);
        $pr->_paid = Shift::paidTimeInSeconds($this->shift->id);
    }

    private function _buildPayrollRecordSegments(PayrollRecords $pr)
    {
        $pr->pay_rate = $this->shift->pay_rate;
        list($pr->_reg, $pr->_ot) = $this->_getRegularHours($pr);
        $pr->regularPaid = $this->_getRegularPaid($pr);
        $pr->overtimePaid = $this->_getOvertimePaid($pr);
    }

    private function _getRegularPaid(PayrollRecords $pr)
    {
        return ($pr->pay_rate * ($pr->_reg / 3600));
    }

    private function _getOvertimePaid(PayrollRecords $pr)
    {
        return (GlobalSetting::get('overtime_multiplier') * $pr->pay_rate * ($pr->_ot  / 3600));
    }

    private function _getRegularHours(PayrollRecords $pr)
    {
        $paid = $pr->_paid / 3600;
        $total = $this->payRangeRecords->totalRegularHours + $paid;
        if ($total <= $this->overtimeThreshold) {
            return [$paid * 3600, 0];
        } else {
            $otOffset = $total - $this->overtimeThreshold;
            $regOffset = $this->overtimeThreshold - $this->payRangeRecords->totalRegularHours;
            return [$regOffset * 3600, $otOffset * 3600];
        }
    }
}
