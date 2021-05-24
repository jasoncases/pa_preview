<?php

namespace Proaction\Domain\Payroll\Service;

use Proaction\System\Resource\Helpers\Arr;

/**
 * Reduces provided timestamp data from a specific shift down to small
 * segments of time which are returned to be stored as a completed
 * payroll record in another database table.
 *
 * ShiftCalculator takes all ts_timesheet rows from a single shift_id
 * and aggregates them into the following fields for ts_payroll_complete
 * _clock: time between clock in and clock out
 * _lunch: time while in lunch state
 * _break: time while in break state
 * _paid:  explicit difference between _clock and _lunch
 *
 * shape of incoming data
 * ----------------------
 * [
 *      [
 *          shift_id    => int
 *          employee_id => int
 *          time_stamp  => string
 *          activity_id => int
 *          unix_ts     => int
 *      ]
 * ]
 */
class ShiftSegmentCalculator
{
    protected $stamps = [];
    public function __construct($timestamps)
    {
        $this->stamps = $timestamps;
        $this->_validate($timestamps);
    }

    public function calc()
    {
        return [
            '_clock' => $this->_calcTotal(),
            '_break' => $this->_calcBreak(),
            '_lunch' => $this->_calcLunch(),
            '_paid' => $this->_calcTotal() - $this->_calcLunch(),
        ];
    }

    private function _calcTotal()
    {
        $clockIn = current($this->extractClockIn());
        $clockOut = current($this->extractClockOut());
        return $clockOut['unix_ts'] - $clockIn['unix_ts'];
    }

    private function _calcLunch()
    {
        $lunchPunches = $this->extractLunches();
        if (empty($lunchPunches) || is_null($lunchPunches)) {
            return 0;
        }
        array_multisort(array_column($lunchPunches, 'activity_id'), SORT_DESC, $lunchPunches);
        $lunchIn = current($lunchPunches);
        $lunchOut = end($lunchPunches);
        return $lunchOut['unix_ts'] - $lunchIn['unix_ts'];
    }

    private function _calcBreak()
    {
        $breaks = $this->extractBreaks();
        if (empty($breaks) || is_null($breaks)) {
            return 0;
        }
        $breakId = 3;
        $acc = 0;
        foreach ($breaks as $break) {
            extract($break);
            $acc += $unix_ts * ($activity_id / $breakId);
        }
        return abs($acc);
    }

    private function _validate($timestamps)
    {
        return ShiftSegmentValidator::validate($timestamps, $this);
    }

    public function extractClockIn()
    {
        return $this->_extractByActionId(1);
    }

    public function extractClockOut()
    {
        return $this->_extractByActionId(0);
    }

    public function extractLunches()
    {
        return $this->_extractByActionId(5);
    }

    public function extractBreaks()
    {
        return $this->_extractByActionId(3);
    }

    private function _extractByActionId($activity_id)
    {
        return array_filter($this->stamps, function ($v, $k) use ($activity_id) {

            return abs($v['activity_id']) == $activity_id;
        }, ARRAY_FILTER_USE_BOTH);
    }
}
