<?php

namespace Proaction\Domain\Timesheets\Resource\Timeline;

use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Users\Resource\UserFactory;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Helpers\Misc;


class TimelineBuilder
{
    protected $range;
    protected $dows = [];

    protected $defaultMin = 6;
    protected $minimumDisplayLength = 12; // hours
    protected $offset = 1; // hours

    protected $min, $max;

    public function __construct($range = null)
    {
        $this->range = $this->_buildRange($range);
        $user = UserFactory::create();
        $this->employee = Employee::find($user->get('employeeId'));
        $this->_buildDays();
        $this->_setMinMax();
    }

    private function _setMinMax()
    {
        $this->min = $this->_setMin();
        $this->max = $this->_setMax();
    }

    private function _setMin()
    {
        $c = [];
        foreach ($this->dows as $day) {
            $c[] = $day->getMinAction();
        }
        $c = array_filter($c);

        if (empty($c)) {
            return $this->defaultMin;
        }

        return min(array_filter($c)) - $this->offset;
    }

    private function _setMax()
    {
        $c = [];
        foreach ($this->dows as $day) {
            $c[] = $day->getMaxAction();
        }

        // clear any null values from c
        $c = array_filter($c);

        if (empty($c)) {
            return $this->min + $this->minimumDisplayLength;
        }

        $max = max($c) + $this->offset;
        $diff = $max - $this->min;
        return $diff < $this->minimumDisplayLength
            ? $this->min + $this->minimumDisplayLength
            : $max;
    }

    private function _buildDays()
    {
        $day = current($this->range);
        while (strtotime($day) < strtotime(end($this->range))) {
            $this->dows[] = new Dow($this->employee, $day);
            $day = date('Y-m-d', strtotime("+1 day", strtotime($day)));
        }
    }

    public function render()
    {
        $c = [];

        $user = UserFactory::create();
        foreach ($this->dows as $day) {
            $dayOutput = $day->getOutput();
            $dayOutput['min'] = $this->min;
            $dayOutput['max'] = $this->max;
            $dayOutput['current_status'] = $user->getTimeclockStatusId();
            $c[] = $dayOutput;
        }

        return array_reverse($c);
    }

    private function _buildRange($range)
    {
        return Misc::getPayrollDateRange($range);
    }
}
