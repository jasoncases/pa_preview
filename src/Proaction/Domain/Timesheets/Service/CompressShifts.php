<?php

namespace Proaction\Domain\Timesheets\Service;

use Proaction\Domain\Payroll\Service\ShiftDetail;
use Proaction\Domain\Timesheets\Model\Timesheet;

use Proaction\System\Resource\Helpers\Arr;

/**
 * Returns User Timesheet hours
 *
 * For reliable Regular/Overtime hours, must only use shifts within the
 * same administrative payroll week
 *
 * format = ['regular', 'overtime', 'total', 'total_paid', 'break', 'lunch']
 */
class CompressShifts{
    private $shifts;
    private $openShift;
    private $closedShifts = [];
    private $compressor;

    public function __construct($shifts)
    {
        ini_set('serialize_precision', -1);
        $this->shifts = $shifts;
        $this->_init();
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getTotalAccumulative() {
        return $this->compressor->getTotalAccumulative();
    }

    public function getOpenShiftHours() {
        return $this->compressor->getOpenShiftHours();
    }

    public function getClosedShiftHours() {
        return $this->compressor->getClosedShiftHours();
    }

    private function _init() {
        foreach ($this->shifts as $shift){
            $this->_filterShift($shift);
        }
        $this->compressor = new ShiftCompressor($this->openShift, $this->closedShifts);
    }

    private function _filterShift($shift) {
        if (boolval($shift['active'])) {
            $this->openShift = Timesheet::getActionsByShiftId($shift['id']);
        } else {
            $this->closedShifts[] = (new ShiftDetail($shift['id']))->getMeta();
        }
    }

}


/**
 * Returns User Timesheet hours for a currently active shift
 *
 * format =
 *      ['regular', 'overtime', 'total', 'total_paid', 'break', 'lunch']
 */
class CompressOpenShift{
    private $actions;

    private $breaks = [];
    private $lunch = [];
    private $clockIn;


    public function __construct($shiftActions)
    {
        $this->actions = $shiftActions ?? [];
        $this->now = time();
        $this->_init();
        if (isset($_GET['ttt'])) {
            Arr::pre($this);
        }
    }

    public function get() {
        return $this->shiftDetails;
    }

    private function _init() {
        if (!empty($this->actions)) {
            $this->_processActions();
            $this->_calculateHours();
        } else {
            $this->_defineDefault();
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    private function _defineDefault() {
        $this->shiftDetails = [
            'total' => 0,
            'total_paid' => 0,
            'regular' => 0,
            'overtime' => 0,
            'break' => 0,
            'total' => 0,
        ];
    }

    private function _calculateHours() {
        $this->shiftDetails['total'] = $this->_total() / 3600;
        $this->shiftDetails['total_paid'] = ($this->_total() - $this->_lunch()) / 3600;
        $this->shiftDetails['regular'] = 0;
        $this->shiftDetails['overtime'] = 0;
        $this->shiftDetails['break'] = $this->_breaks() / 3600;
        $this->shiftDetails['total'] = $this->_lunch() / 3600;
    }

    private function _processActions() {
        foreach ($this->actions as $action) {
            $this->_filterAction($action);
        }
    }

    private function _filterAction($action) {
        extract($action);
        if ($activity_id == 1) {
            $this->clockIn = $action;
        } else if (abs($activity_id) == 3) {
            $this->breaks[] = $action;
        } else if (abs($activity_id) == 5) {
            $this->lunch[] = $action;
        }
    }

    private function _total() {
        return $this->now - $this->clockIn['unix_ts'];
    }

    private function _lunch() {
        if (count($this->lunch) <= 0) {
            return 0;
        } else if (count($this->lunch) == 1) {
            return $this->now - current($this->lunch)['unix_ts'];
        } else if (count($this->lunch) == 2) {
            return abs(current($this->lunch)['unix_ts'] - end($this->lunch)['unix_ts']);
        } else {
            throw new \Exception\ValueTypeException('Too many lunch shifts');
        }
    }

    private function _breaks() {
        $breakUnix = array_column($this->breaks, 'unix_ts');
        if (count($breakUnix) <= 0) {
            return 0;
        } else if (boolval(count($breakUnix) % 2)) {
            $breakUnix[] = $this->now;
        }
        return $this->_compressBreaks($breakUnix);
    }

    private function _compressBreaks($arrOfUnix) {
        $acc = 0;
        for ($ii = 0; $ii < count($arrOfUnix); $ii++) {
            $acc += boolval($ii % 2) ? -$arrOfUnix[$ii] : $arrOfUnix[$ii];
        }
        return abs($acc);
    }

    private function _format($num) {
        return number_format($num/3600, 2, '.', '');
    }
}
