<?php

namespace Proaction\Domain\Payroll\Resource\ReportBuilder;

use Illuminate\Database\Eloquent\Collection;
use Proaction\Domain\Clients\Model\GlobalSetting;
use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\Domain\Payroll\Model\PayrollRecords;
use Proaction\Domain\Timesheets\Resource\ActiveShiftBuilder;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Helpers\Misc;

/**
 * Aggregate employee payroll information.
 *
 * Displays records for all employees that have shifts within the given
 * range. A third optional argument allows for filtering, the details of
 * which are still being processed.
 *
 * If a singleEmployeeToggle is set to true and an employeeId is given
 * where the employee has no records, a row will be displayed with the
 * employee's first/last name, but 0 for all values
 */
class PayrollReportBuilder
{
    private $employees;

    private $from;
    private $to;

    // >>> options to be set via filters, single employee has priority
    // >>> so if both sets of options are sent, user will only be able
    // >>> to see their own payroll data

    // get the data of a single employee
    private $opt_singleEmployeeToggle = false;
    private $opt_singleEmployeeId = null;

    // get the data for an array of department ids
    private $opt_departmentSearchToggle = false;
    private $opt_departments = null;

    private $opt_removeEmployeesWithEmptyRecordSets = true;

    public function __construct($from, $to, $options = [])
    {
        $this->from = $from;
        $this->to = $to;
        $this->_initializeOptions($options);
        $this->_init();
    }

    /**
     * Return the employee records
     *
     * @return Collection
     */
    public function get()
    {
        return $this->employees;
    }

    /**
     * Load the employees and records by provided options
     *
     * @return void
     */
    private function _init()
    {
        $this->employees = $this->_getEmployees();
        $this->_getRecordsByEmployees();
    }

    /**
     * Return the employees based on provided filters/defaults
     *
     * @return Collection
     */
    private function _getEmployees()
    {
        if ($this->opt_singleEmployeeToggle) {
            // return Collection of a single EmployeeView record
            return EmployeeView::where('id', $this->opt_singleEmployeeId)->get();
        } else if ($this->opt_departmentSearchToggle) {
            // return Collection of EmployeeView records by dept id
            return EmployeeView::whereIn('department_id', $this->opt_departments)->get();
        } else {
            // default return all employees with records in range
            return EmployeeView::getEmployeesWithActivePayrollRecordsByRange([$this->from, $this->to]);
        }
    }

    /**
     * Add a 'payroll' attr to each employee. If no records are found,
     * they are stripped out, can be toggled w/ options above
     *
     * @return void
     */
    private function _getRecordsByEmployees()
    {
        foreach ($this->employees as $emp) {
            $this->_getEmployeesPayrollRecords($emp);
        }
    }

    /**
     * Add payroll attr to provided EmployeeView model OR filter it out
     * if the payroll records are empty
     *
     * @param EmployeeView $emp
     * @return void
     */
    private function _getEmployeesPayrollRecords(EmployeeView $emp)
    {
        $records = $this->_getAllEmployeePayrollData($emp->id, $this->from, $this->to);
        if (!$records->isEmpty()) {
            $emp->payroll = $this->_compressRecords($records);
        } else {
            // contained method to deal with filtering logic, as it may
            // get complex
            $this->_filterOutEmptyRecords($emp);
        }
    }

    private function _getAllEmployeePayrollData($empId, $from, $to)
    {
        $recs = PayrollRecords::getEmployeeTotalsByRange($empId, $from, $to);

        return $recs;
    }


    /**
     * Remove a record from the parent ::employees Collection based on
     * provided options.
     *
     * Removing empty records is the default, but is overridden when
     * displaying a single employee's records.
     *
     * @param EmployeeView $emp
     * @return void
     */
    private function _filterOutEmptyRecords(EmployeeView $emp)
    {
        if ($this->opt_removeEmployeesWithEmptyRecordSets) {
            // if displaying a single employee, always display result
            // even if empty. Prevents the user from seeing an emtpy
            // screen which could be interpreted as an error
            if (!$this->opt_singleEmployeeToggle) {
                $this->employees = $this->employees->filter(function ($item) use ($emp) {
                    return $item->id != $emp->id;
                });
            }
        }
    }

    /**
     * Compress Collection of records to summarized data for a single
     * employee
     *
     * @param Collection $records
     * @return PayrollRecords
     */
    private function _compressRecords(Collection $records)
    {
        $compressedRecord = new PayrollRecords;
        $compressedRecord->totalRegularHours = Misc::money($records->sum('_reg') / 3600);
        $compressedRecord->totalOvertimeHours = Misc::money($records->sum('_ot') / 3600);
        $compressedRecord->totalRegularPaid = Misc::money($records->sum('regularPaid'));
        $compressedRecord->totalOvertimePaid = Misc::money($records->sum('overtimePaid'));
        $compressedRecord->totalHours = Misc::money($compressedRecord->totalRegularHours + $compressedRecord->totalOvertimeHours);
        $compressedRecord->totalPaid = Misc::money($compressedRecord->totalRegularPaid + $compressedRecord->totalOvertimePaid);
        $compressedRecord->regularRate = $this->_getRegularRate($records->pluck('pay_rate')->toArray());
        $compressedRecord->overtimeRate = $this->_getOvertimeRate($records->pluck('pay_rate')->toArray());
        $compressedRecord->range_clock = Misc::money($records->sum('_clock') / 3600);
        $compressedRecord->range_break = Misc::money($records->sum('_break') / 3600);
        $compressedRecord->range_lunch = Misc::money($records->sum('_lunch') / 3600);
        $compressedRecord->range_paid = Misc::money($records->sum('_paid') / 3600);
        return $compressedRecord;
    }

    /**
     * Undocumented function
     *
     * @param array $rateArray
     * @return string
     */
    private function _getRegularRate($rateArray)
    {
        $unique = array_unique($rateArray);
        if (count($unique) == 1) {
            return Misc::money(current($unique));
        }
        return Misc::money(max($unique)) . '*';
    }

    /**
     * Undocumented function
     *
     * @param array $rateArray
     * @return string
     */
    private function _getOvertimeRate($rateArray)
    {
        $unique = array_unique($rateArray);
        if (count($unique) == 1) {
            return Misc::money(current($unique) * GlobalSetting::get('overtime_multiplier'));
        }
        return Misc::money(max($unique) * GlobalSetting::get('overtime_multiplier')) . '*';
    }

    /**
     * Set provided options to top level class attrs
     *
     * @param array $options
     * @return void
     */
    private function _initializeOptions(array $options)
    {
        if (isset($options['singleEmployeeToggle'])) {
            $this->opt_singleEmployeeToggle = $options['singleEmployeeToggle'];
            $this->opt_singleEmployeeId = $options['employeeId'];
        }
        if (isset($options['departmentSearchToggle'])) {
            $this->opt_departmentSearchToggle = $options['departmentSearchToggle'];
            $this->opt_departments = $options['departments'];
        }
    }
}
