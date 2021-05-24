<?php

namespace Proaction\Domain\Payroll\Resource;

use Proaction\Domain\Payroll\Model\PayrollRecords;

/**
 * CREATE OR REPLACE VIEW v_payroll_records AS
 * SELECT a.*, b.pay_rate, c.first_name, c.last_name, b.created_at as shift_created_at, UNIX_TIMESTAMP(b.created_at) as unix_created, d.department_id FROM ts_payroll_completed as a
 * LEFT JOIN ts_shifts b ON b.id=a.shift_id
 * LEFT JOIN employees c ON c.id=a.employee_id
 * LEFT JOIN employee_departments d ON d.employee_id=a.employee_id;
 */

class Payroll
{

    protected $_records = [];
    protected $_searchedFrom;
    protected $_searchedTo;
    protected $_options;

    public function __construct($from, $to, $options = [])
    {
        $this->_searchedFrom = $from;
        $this->_searchedTo = $to ?? date('Y-m-d 23:59:59');
        $this->_options = $options;
    }

    public function render()
    {
        $this->_renderHeader();
        foreach ($this->_records as $rec) {
            $rec->render();
        }
        $this->payrollSummary->render();
        $this->_renderClosingTag();
        $this->renderFooter();
    }

    private function renderFooter()
    {
        return view('Domain.Payroll.payroll_footer');
    }

    public function load()
    {
        $this->_loadRecords();
        return $this;
    }

    private function _loadRecords()
    {
        $this->_getAllRecords();
        $this->_createRecords();
        $this->payrollSummary = $this->_createPayrollSummary(new PayrollSummary());
        $this->_getSummaryData();
        // print_r($this);
    }

    private function _getSummaryData()
    {
        foreach ($this->_records as $rec) {
            $this->payrollSummary->addSummaryData($rec->getSummary());
        }
    }
    private function _getAllRecords()
    {
        $this->stamps = PayrollRecords::getPayrollRecords($this->_searchedFrom, $this->_searchedTo, $this->_options);
        // echo '<pre>';
        // print_r($this->stamps);
    }

    private function _createRecords()
    {
        $ids = $this->_getAllUniqueEmployees();
        foreach ($ids as $empId) {
            $this->_records[] = $this->_createRecord(new Record($this->_getRecordByEmployeeId($empId), $this->_searchedFrom, $this->_options));
        }

    }
    private function _getAllUniqueEmployees()
    {
        $stamps = empty($this->stamps) ? [] : $this->stamps;
        return array_unique(array_column($stamps, 'employee_id'));
    }

    private function _getRecordByEmployeeId($employee_id)
    {
        return array_filter($this->stamps, function ($v, $k) use ($employee_id) {
            return $v['employee_id'] == $employee_id;
        }, ARRAY_FILTER_USE_BOTH);
    }

    private function _createPayrollSummary( $ps)
    {
        $ps->Payroll = $this;
        return $ps;
    }
    private function _createRecord( $record)
    {
        // $record->Payroll = $this;
        return $record;
    }

    private function _renderHeader()
    {
        echo '<div class="payroll-row-container flex-col flex-start">';

        echo "\t\t\t" . '<div class="flex-row flex-start pr-row-border pr-header">
                            <div class="pr-cell flex-col flex-center pr-cell-fitcontent text-center  " data-sort="id" id="ui:payroll:sortableHeader">#</div>
                            <div class="pr-cell flex-col flex-center pr-cell-name                " data-sort="last_name" id="ui:payroll:sortableHeader">Last Name</div>
                            <div class="pr-cell flex-col flex-center pr-cell-name                " data-sort="first_name" id="ui:payroll:sortableHeader">First Name</div>
                            <div class="pr-cell flex-col flex-center pr-cell-hours col-yellow text-right    " data-sort="regular_hours" id="ui:payroll:sortableHeader">Reg Hrs</div>
                            <div class="pr-cell flex-col flex-center pr-cell-rate col-blue text-right     " data-sort="regular_rate" id="ui:payroll:sortableHeader">$/HR</div>
                            <div class="pr-cell flex-col flex-center pr-cell-currency col-green text-right " data-sort="regular_paid" id="ui:payroll:sortableHeader">Reg $</div>
                            <div class="pr-cell flex-col flex-center pr-cell-hours col-yellow text-right    " data-sort="overtime_hours" id="ui:payroll:sortableHeader">O/T Hrs</div>
                            <div class="pr-cell flex-col flex-center pr-cell-rate col-blue text-right vanish-standard    " data-sort="overtime_rate" id="ui:payroll:sortableHeader">OT/hr</div>
                            <div class="pr-cell flex-col flex-center pr-cell-currency col-green text-right " data-sort="overtime_paid" id="ui:payroll:sortableHeader">O/T $</div>
                            <div class="pr-cell flex-col flex-center pr-cell-hours col-yellow text-right    " data-sort="total_hours" id="ui:payroll:sortableHeader">Ttl Hrs</div>
                            <div class="pr-cell flex-col flex-center pr-cell-currency col-green text-right " data-sort="total_paid" id="ui:payroll:sortableHeader">Ttl Pay</div>
                            <div class="pr-cell flex-col flex-center pr-cell-status text-center  " data-sort="alert" ><i class="fas fa-exclamation"></i></div>
                            <div class="pr-cell flex-col flex-center pr-cell-status text-center  " data-sort="edit" ><i class="far fa-exchange"></i></div>
                            <div class="pr-cell flex-col flex-center pr-cell-status text-center  " data-sort="globe" ><i class="fal fa-globe"></i></div>
                            <div class="pr-cell flex-col flex-center pr-cell-status text-center  " data-sort="flag" ><i class="fas fa-flag"></i></div>
                            <div class="pr-cell flex-col flex-center pr-cell-status text-center  " data-sort="edit" ><i class="far fa-comment-alt"></i></div>
                            <div class="pr-cell flex-col flex-center pr-cell-status text-center  " data-sort="edit" ><i class="far fa-check-double"></i></div>
                        </div>';
    }
    private function _renderClosingTag()
    {
        echo '</div>';
    }
}
