<?php

namespace Proaction\Domain\Payroll\Resource;

use Proaction\Resource\Payroll\Summary;

class Record
{

    protected $_shifts;
    protected $_paid;
    protected $_break;
    protected $_lunch;
    protected $_clock;
    protected $_rate;
    protected $_reg;
    protected $_ot;
    protected $_intRate;
    protected $employee = [];
    protected $_shiftChildren = [];
    protected $_summary;

    public function __construct(array $shifts, string $offset, $options=[])
    {

        $this->_shifts = $shifts;
        // Arr::pre($shifts);
        $this->_offset = strtotime($offset);
        $this->singleRecord = array_search('employee_id', $options);
        $this->_init();
    }

    public function render($open = false)
    {
        echo "\n\n\n<!-- BEGIN RENDER ROW -->\n\n\n";
        $this->row->render();
        $this->summary->render();
        $this->shiftContainer->render();
        $this->row->renderClose();
        echo "</div>\n\n\n<!-- END RENDER ROW -->\n\n\n";
    }

    private function _init()
    {
        $this->_populateValues();
        // print_r($this);
    }

    private function _populateValues()
    {
        $this->_paid = $this->_getValue('_paid');
        $this->_break = $this->_getValue('_break');
        $this->_lunch = $this->_getValue('_lunch');
        $this->_clock = $this->_getValue('_clock');
        $this->_reg = $this->_getValue('_reg');
        $this->_ot = $this->_getValue('_ot');
        $this->_rate = $this->_getPayrate();
        $this->employee = $this->_getEmployee();
        $this->summary = $this->_createSummary(new Summary($this->_getSummary()));
        $this->row = $this->_createRecordRow(new RecordRow(array_merge($this->_getRecordRowData(), $this->employee)));
        $this->shiftContainer = new ShiftContainer($this->employee);
        // echo '<pre>';
        // print_r($this);
    }

    private function _getRecordRowData()
    {
        /**
         * do the rounding so all numbers multiplied are hundredths and
         * then do formatting/rounding at the end number_format handles
         * rege rounding, round() is not needed below.
         */
        $regPay = round($this->_reg / 3600, 2) * $this->_intRate;
        $otPay = round($this->_ot / 3600, 2) * round($this->_intRate * 1.5, 2);

        return [
            'overtime_rate' => number_format($this->_intRate * 1.5, 2, '.', ''),
            'current_rate' => $this->_rate,
            'totalRegularHours' => number_format($this->_reg / 3600, 2, '.', '') ?? '0.00',
            'totalOvertimeHours' => number_format($this->_ot / 3600, 2, '.', '') ?? '0.00',
            'totalRegularPay' => number_format($regPay, 2, '.', '') ?? '0.00',
            'totalOvertimePay' => number_format($otPay, 2, '.', '') ?? '0.00',
            'totalHoursWorked' => number_format($this->_paid / 3600, 2, '.', '') ?? '0.00',
            'totalPay' => number_format($regPay + $otPay, 2, '.', '') ?? '0.00',
            'open' => boolval($this->singleRecord !== false) ? 'pr-record-open' : '',
        ];
    }

    private function _getValue($prop)
    {
        return array_sum(
            array_column(
                $this->_shifts,
                $prop
            )
        );
    }

    private function _getEmployee()
    {
        return [
            'id' => current(array_unique(array_column($this->_shifts, 'employee_id'))),
            'first_name' => current(array_unique(array_column($this->_shifts, 'first_name'))),
            'last_name' => current(array_unique(array_column($this->_shifts, 'last_name'))),
            'department_id' => current(array_unique(array_column($this->_shifts, 'department_id'))),
        ];
    }

    private function _getPayrate()
    {
        $rate = array_unique(array_column($this->_shifts, 'pay_rate'));
        // print($rate);
        $this->_intRate = end($rate);
        return count($rate) > 1 ? end($rate) . '*' : end($rate);
    }

    private function _createSummary(Summary $summary)
    {
        return $summary;
    }

    private function _createRecordRow(RecordRow $row)
    {
        return $row;
    }

    public function getSummary()
    {
        return $this->_getRecordRowData();
    }

    private function _getSummary()
    {
        return [
            'total_clock' => number_format($this->_clock / 3600, 2, '.', ''),
            'total_lunch' => number_format($this->_lunch / 3600, 2, '.', ''),
            'total_break' => number_format($this->_break / 3600, 2, '.', ''),
            'total_paid' => number_format($this->_paid / 3600, 2, '.', ''),
        ];
    }
}
