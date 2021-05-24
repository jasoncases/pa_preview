<?php

namespace Proaction\Domain\Payroll\Resource\ReportBuilder;

use Illuminate\Database\Eloquent\Collection;
use Proaction\Domain\Payroll\Model\PayrollComplete;
use Proaction\Domain\Payroll\Model\PayrollRecords;
use Proaction\Domain\Payroll\ViewBuilders\PayrollReport;
use Proaction\Domain\Timesheets\Model\Shift;
use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\Domain\Worktypes\Model\Worktype;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Helpers\Misc;

class ShiftActionFormatter
{

    public static function go(Collection $actions)
    {
        return (new static)->_process($actions);
    }

    private function _process(Collection $actions)
    {
        $shift = new Shift;
        $shift->clockIn = $this->_formatStamp($actions->shift());
        $shift->id = $shift->clockIn->shift_id;
        $shift->employeeId = $shift->clockIn->employee_id;
        $shift->clockOut = $this->_getClockOut($actions);
        $shift->active = !boolval($shift->clockOut);
        $shift->pairs = $this->_processTimestampPairedActions($actions);
        $shift->nextDay = $this->_isNextDay($shift);
        $shift->totalHours = $this->_getTotalHours($shift);
        $shift->date = date("m/d/Y", strtotime($shift->clockIn->time_stamp));
        return $shift;
    }

    /**
     * Break the given timesheet actions down into managable pairs with
     * an open and a close action. Return as a Collection of pairs for
     * iterating through
     *
     * @param Collection $actions
     * @return void
     */
    private function _processTimestampPairedActions(Collection $actions)
    {
        $c = new Collection();
        foreach ($actions->values() as $index => $item) {
            if (!isset($item->hasBeenFiltered) && !boolval($item->hasBeenFiltered)) {
                // ignore if the activity id is 0, i.e., clockout
                if ($item->activity_id != 0) {
                    // treat each pair as a mini "Shift" model with an
                    // in and an out action
                    $pair = new Shift;
                    $pair->open = $this->_formatStamp($item);
                    // next is the next iterable element, if it doesn't
                    // exist, coalesce to an empty Timesheet model
                    // set to close and push to the collection
                    $pair->close = $this->_formatStamp($actions->get(++$index) ?? new Timesheet);
                    $c->push($pair);
                }
            }
        }
        return $c;
    }

    private function _formatStamp(Timesheet $stamp)
    {

        if (isset($stamp->time_stamp)) {
            $label = Worktype::where('actionId', $stamp->activity_id)->first();
            if (!$label) {
                Arr::pre($stamp);
                die();
            }
            $stamp->label = $this->_formatLabelText($label->text);
            $stamp->displayTime = date('H:i:s', $stamp->unix_ts);
            $stamp->displayDate = date('m/d/Y', $stamp->unix_ts);
            $stamp->sqlformatDate = date('Y-m-d', $stamp->unix_ts);
            $this->_formatFormData($stamp);
        }
        // set filter toggle to true and iterate, this will
        // allow the next element to be ignored
        $stamp->hasBeenFiltered = true;
        return $stamp;
    }

    private function _formatFormData(Timesheet $stamp)
    {
        list($stamp->h, $stamp->i, $stamp->s) = explode(':', $stamp->displayTime);
        list($stamp->m, $stamp->d, $stamp->Y) = explode('/', $stamp->displayDate);
    }

    private function _formatLabelText($str)
    {
        $str = str_replace('Start', '', $str);
        $str = str_replace('End', '', $str);
        $str = str_replace('start', '', $str);
        $str = str_replace('end', '', $str);
        return $str;
    }

    private function _getClockOut(Collection $actions)
    {
        if (!$actions->isEmpty()) {
            $last = $actions->last();
            if ($last->activity_id === 0) {
                return $this->_formatStamp($last);
            }
        }
        return null;
    }

    private function _isNextDay($shift)
    {
        if (!$shift->active) {
            return (date("Y-m-d", strtotime($shift->clockIn->time_stamp))
                != date("Y-m-d", strtotime($shift->clockOut->time_stamp)));
        }
        return false;
    }

    private function _getTotalHours($shift)
    {
        // If shift is not active, return the duration
        if (!$shift->active) {
            $record = $this->_getPayrollRecord($shift);
            return Misc::money($record->_paid / 3600);
        } else {
            return Misc::money(Shift::paidTimeInSeconds($shift->id) / 3600);
        }
    }

    /**
     * Get the payroll record, if it doesn't exist, create it, then
     * return the result
     *
     * @param Shift $shift
     * @return PayrollRecords
     */
    private function _getPayrollRecord(Shift $shift)
    {
        $record = PayrollRecords::where('shift_id', $shift->id)->first();
        if (!$record) {
            PayrollComplete::new__closeShift(Shift::find($shift->id));
            $record = PayrollRecords::where('shift_id', $shift->id)->first();
        }
        return $record;
    }
}
