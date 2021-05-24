<?php

namespace Proaction\Domain\Timesheets\Service;

use function Proaction\System\Lib\money;



class ShiftCompressor{

    private $openShift;
    private $closedShifts = [];

    private $maxreg = 40;

    public function __construct($openShift = null, $closedShifts = [])
    {
        $this->openShift = $openShift;
        $this->closedShifts = $closedShifts;
    }

    /**
     *
     * @return array
     */
    public function getOpenShiftHours() {
        return (new CompressOpenShift($this->openShift))->get();
    }

    /**
     *
     * @return array
     */
    public function getClosedShiftHours() {
        return [
            'total' => $this->_sumCol('clock', $this->closedShifts),
            'total_paid' =>$this->_sumCol('paid', $this->closedShifts),
            'regular' => $this->_sumCol('reg', $this->closedShifts),
            'overtime' => $this->_sumCol('ot', $this->closedShifts),
            'break' => $this->_sumCol('break', $this->closedShifts),
            'lunch' => $this->_sumCol('lunch', $this->closedShifts),
        ];
    }

    /**
     *
     * @return array
     */
    public function getTotalAccumulative() {
        return $this->_combine(
            $this->getOpenShiftHours(),
            $this->getClosedShiftHours()
        );
    }

    /**
     * Sums the values in the array column provided and provides a
     * 'money' formatted return string
     *
     * @param string $key
     * @param array $array
     * @return string
     */
    private function _sumCol($key, $array){
        return money(array_sum(array_column($array, $key)));
    }

    /**
     * Aggregates open and closed data arrays
     *
     * @param array $open
     * @param array $closed
     * @return array
     */
    private function _combine($open, $closed){
        [$regular, $overtime] = $this->_calculatePaidTypes($open, $closed);
        return [
            'total' => money($open['total'] + $closed['total']),
            'total_paid' => money($open['total_paid'] + $closed['total_paid']),
            'regular' =>  money($regular),
            'overtime' => money($overtime),
            'break' => money($open['break'] + $closed['break']),
            'lunch' => money($open['lunch'] + $closed['lunch']),
        ];
    }

    /**
     * Sums regular versus overtime hours, method steps are commented
     * inline
     *
     * @param float $open
     * @param float $closed
     * @return array <(float) $regular, (float) $overtime>
     */
    private function _calculatePaidTypes($open, $closed) {
        $reg = 0;
        $ot = 0;
        /**
         * If user's closed hours are already >= (should never be grtr,
         * but it's included anyway), set the regular hours to 40 and
         * set ot to closed.overtime + open.total_paid
         */
        if ($closed['regular'] >= $this->maxreg) {
            $reg = $this->maxreg;
            $ot = $closed['overtime'] + $open['total_paid'];
        } else {
            /**
             * If regular are under 40 we need to account for the state
             * when the new hours added will take the user's regular hrs
             * over the maxreg threshold, so we set regular to 40
             * and overtime is the offset between total hours and maxreg
             */
            if ($closed['regular'] + $open['total_paid'] >= $this->maxreg) {
                // closed regular = 37
                // open paid = 8
                // ot should be 5, 37 + 8 = 45. 45 - 40 = 5
                $reg = $this->maxreg;
                $ot = ($closed['regular'] + $open['total_paid']) - $this->maxreg;
            } else {
                // ... otherwise, it's straightforward addition of reg
                // and paid
                $reg = $closed['regular'] + $open['total_paid'];
            }
        }
        // return the values as an array
        return [$reg, $ot];
    }
}
