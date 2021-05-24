<?php

namespace Proaction\Domain\Timesheets\Resource;


/**
 * Timesheet activity reducer. Class is given an array of timesheet
 * actions and they are reduced to an array of their activity
 */
class Calc
{
    private $stamps = [];

    private $clockin, $clockout, $breakin = [], $breakout = [], $lunchin, $lunchout;

    private $shiftSec, $breakSec, $lunchSec, $paidSec;
    private $shift = 0, $break = 0, $lunch = 0, $paid = 0;

    private $hour = 3600;

    private $output = [];

    /**
     * Undocumented function
     *
     * @param [type] $stamps
     */
    public function __construct($stamps = [])
    {
        $this->stamps = $stamps;
    }

    public function calc()
    {
        $this->_mapStamps();
        $this->_calcShiftDuration();
        $this->_calcBreakDuration();
        $this->_calcLunchDuration();
        $this->_calcPaidDuration();
        return $this;
    }

    public function output()
    {
        $currStamp = current($this->stamps);
        return [
            'employee_id' => $currStamp['employee_id'],
            'first_name' => $currStamp['first_name'],
            'last_name' => $currStamp['last_name'],
            '_clock' => $this->shiftSec,
            '_break' => $this->breakSec,
            '_lunch' => $this->lunchSec,
            '_paid' => $this->paidSec,
            '_rate' => $currStamp['_rate'],
        ];
    }

    private function _calcShiftDuration()
    {
        //
        $start = $this->clockin['unix_ts'];
        $end = $this->clockout['unix_ts'] ?? time();
        $this->shiftSec = $end - $start;
        $this->shift = number_format($this->shiftSec / $this->hour, 2, '.', '');
    }

    private function _calcBreakDuration()
    {
        //
        if (empty($this->breakout)) {
            $this->breakSec = 0;
            $this->break = 0;
            return;
        }

        $breakoutUnix = array_column($this->breakout, 'unix_ts');
        $breakinUnix = count($this->breakin) ? array_column($this->breakin, 'unix_ts') : [time()];

        if (count($breakoutUnix) != count($breakinUnix)) {
            $breakinUnix[] = time();
        }

        $this->breakSec = array_sum($breakinUnix) - array_sum($breakoutUnix);
        $this->break = number_format($this->breakSec / $this->hour, 2, '.', '');
    }

    private function _calcLunchDuration()
    {
        //
        if (is_null($this->lunchout)) {
            $this->lunchSec = 0;
            $this->lunch = 0;
            return;
        }
        $start = $this->lunchout['unix_ts'];
        $end = $this->lunchin['unix_ts'] ?? time();
        $this->lunchSec = $end - $start;
        $this->lunch = number_format($this->lunchSec / $this->hour, 2, '.', '');
    }

    private function _calcPaidDuration()
    {
        //
        $this->paidSec = $this->shiftSec - $this->lunchSec;
        $this->paid = $this->shift - $this->lunch;
    }



    private function _mapStamps()
    {
        foreach ($this->stamps as $k => $stamp) {
            $this->_switchStampActivity($stamp);
        }
    }



    private function _switchStampActivity($stamp)
    {
        extract($stamp);
        switch ($activity_id) {
            case '1':
                $this->clockin = $stamp;
                break;
            case '0':
                $this->clockout = $stamp;
                break;
            case '3':
                $this->breakout[] = $stamp;
                break;
            case '-3':
                $this->breakin[] = $stamp;
                break;
            case '5':
                $this->lunchout = $stamp;
                break;
            case '-5':
                $this->lunchin = $stamp;
                break;
            default:
                $this->corral[] = $stamp;
        }
    }
}
