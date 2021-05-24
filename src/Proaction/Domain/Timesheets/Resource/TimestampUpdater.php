<?php

namespace Proaction\Domain\Timesheets\Resource;

use Exception;
use Proaction\Domain\Payroll\Model\PayrollComplete;
use Proaction\Domain\Timesheets\Model\Shift;
use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\Domain\Timesheets\Model\TimesheetCommentChain;
use Proaction\Domain\Timesheets\Model\TimestampAudit;
use Proaction\System\Resource\Regex\RegexHandler;
use Proaction\System\Resource\Status\FlashAlert;

/**
 * Update a single timestamp record.
 *
 * from route: /timestamp_edit/{id}
 */
class TimestampUpdater
{

    protected $timestamp;
    protected $shift;
    protected $props;
    protected $originTimestamp;

    public function __construct($id, $props)
    {
        $this->timestamp = $this->_validateTimestamp($id);
        $this->shift = Shift::find($props['shift_id']);
        $this->props = $props;
        $this->timestamp->time_stamp = $this->_reassembleTimestamp();
        $this->timestamp->unix_ts = strtotime($this->timestamp->time_stamp);
        $this->originTimestamp = $props['__origin_date'] . ' ' . $props['__origin_time'];
    }

    /**
     * Public initiator method
     *
     * @return void
     */
    public function process()
    {
        $this->_validate();
        $this->_updateTimestampRecord($this->timestamp);
        $this->_afterUpdate();
        $this->_setResponse();
    }

    /**
     * Capture and perform validation on the requested timestamp.
     *
     *
     * @param int $id
     * @return Timesheet
     */
    private function _validateTimestamp($id)
    {
        $ts = Timesheet::find($id);
        if (!$ts) {
            throw new Exception('Timestamp not found');
        }
        return $ts;
    }

    /**
     * Extract the values from the given props and reassemble into a SQL
     * formatted string
     *
     * @return void
     */
    private function _reassembleTimestamp()
    {
        extract($this->props);
        return "$year-$month-$day $hours:$minutes:$seconds";
    }

    /**
     * Save the changes to the Timesheet model.
     *
     * @param Timesheet $t
     * @return Timesheet
     */
    private function _updateTimestampRecord(Timesheet $t)
    {
        return $t->save();
    }

    /**
     * Create a success flash alert.
     *
     * @return void
     */
    private function _setResponse()
    {
        return new FlashAlert('Timestamp updated successfully');
    }

    /**
     * Run these methods as a secondary script after the timestamp is
     * updated.
     *
     * @return void
     */
    private function _afterUpdate()
    {
        $this->_recalculateShiftTotals();
        $this->_createAuditTrail();
        $this->_createEditCommentChain();
    }

    /**
     * If the shift is closed, update the PayrollCompete record.
     *
     * @return void|PayrollComplete
     */
    private function _recalculateShiftTotals()
    {

        if (!Shift::isOpen($this->timestamp->shift_id)) {
            $shift = Shift::find($this->timestamp->shift_id);
            return PayrollComplete::new__recalculate($shift);
        }
    }

    /**
     * Create a TimestampAudit record.
     *
     * @return TimestampAudit
     */
    private function _createAuditTrail()
    {
        return TimestampAudit::newAudit($this->timestamp);
    }

    /**
     * Create a comment chain record, for the payroll report.
     *
     * @return TimesheetCommentChain
     */
    private function _createEditCommentChain()
    {
        $o_timestamp = $this->props['__origin_date'] . ' ' . $this->props['__origin_time'];
        return TimesheetCommentChain::timestampEdit($this->timestamp, $o_timestamp);
    }

    /**
     * Check for paradox and conflicting timestamps.
     *
     * @return void
     */
    private function _validate()
    {
        $this->_newTimestampIsValidTimestamp();
        $this->_newTimestampHasNotChanged();
        $this->_adjustedTimeIsInConflictWithPreviousTimestamp();
        $this->_adjustedTimeIsInConflictWithNextTimestamp();
    }

    /**
     * Check that the provided timestamp does not conflict with the prev
     * timestamp record.
     *
     * @return void
     */
    private function _adjustedTimeIsInConflictWithPreviousTimestamp()
    {
        $prev = Timesheet::getPrevTimestamp($this->timestamp);
        // only check conflict if a prev stamp exists, else no conflict
        if (boolval($prev)) {
            if ($this->timestamp->unix_ts < $prev->unix_ts) {
                throw new Exception("Conflicting time with previous timestamp");
            }
        }
    }

    /**
     * Check that the provided timestamp does not conflict with the next
     * timestamp record.
     *
     * @return void
     */
    private function _adjustedTimeIsInConflictWithNextTimestamp()
    {
        $next = Timesheet::getNextTimestamp($this->timestamp);
        // only check conflict if a next stamp exists, else no conflict
        if (boolval($next)) {
            if ($this->timestamp->unix_ts > $next->unix_ts) {
                throw new Exception("Conflicting time with next timestamp");
            }
        }
    }

    /**
     * If there was no change, throw an exception that there is nothing
     * to change.
     *
     * @return void
     */
    private function _newTimestampHasNotChanged()
    {
        if ($this->originTimestamp === $this->timestamp->time_stamp) {
            throw new Exception("Nothing to change");
        }
    }

    /**
     * Confirm the proper timestamp shape.
     *
     * @return void
     */
    private function _newTimestampIsValidTimestamp()
    {
        if (!RegexHandler::isTimestamp($this->timestamp->time_stamp)) {
            throw new Exception("Invalid timestamp format given");
        }
    }
}
