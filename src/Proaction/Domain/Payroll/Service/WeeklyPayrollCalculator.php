<?php

namespace Proaction\Domain\Payroll\Service;

use Proaction\Domain\Clients\Model\GlobalSetting;

/**
 * Takes an array of timesheet arrays, aggregating them down to calc
 * the regular and overtime hours
 *
 * data in =>
 * id
 * _paid
 *
 * data out =>
 * id
 * _paid
 * _reg
 * _ot
 */
class WeeklyPayrollCalculator
{
    private $maxRegularTimeInSeconds;
    private $shifts;
    private $regularHours = 0;
    private $overtimeHours = 0;

    public function __construct($shifts)
    {
        $this->shifts = $shifts;
        $this->_setConfigOptions();
    }


    public function calc()
    {
        $c = [];
        foreach ($this->shifts as $shift) {
            $paid = $shift['_paid'];
            [$reg, $ot] = $this->_calculateHours($paid);
            $this->regularHours += $reg;
            $this->overtimeHours += $ot;
            $c[] = [
                'id' => $shift['id'],
                '_reg' => $reg,
                '_ot' => $ot,
            ];
        }
        return $c;
    }

    private function _setConfigOptions()
    {
        $this->maxRegularTimeInSeconds = GlobalSetting::get('payroll_max_regular_time_in_seconds') ?? $this->maxRegularTimeInSeconds;
    }

    private function _calculateHours($paid)
    {
        $reg = 0;
        $ot = 0;
        if ($this->regularHours >= $this->maxRegularTimeInSeconds) {
            return [$reg, $paid];
        } else {
            if ($this->regularHours + $paid > $this->maxRegularTimeInSeconds) {
                $offet = $this->maxRegularTimeInSeconds - $this->regularHours;
                return [$offet, $paid - $offet];
            } else {
                return [$paid, $ot];
            }
        }
    }
}
