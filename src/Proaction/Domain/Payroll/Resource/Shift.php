<?php

namespace Proaction\Domain\Payroll\Resource;

use Proaction\Domain\Display\DisplayVersionTwo;
use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\System\Resource\Helpers\Arr;

class Shift
{

    private $id, $employee_id, $pay_rate;
    private $clockIn;
    private $clockOut;
    private $stamps;

    public function __construct(array $shift)
    {
        extract($shift);
        $this->id = $id;
        $this->employee_id = $employee_id;
        $this->pay_rate = $pay_rate;

        $this->_init();
        // echo '<pre>';
        // print_r($this);
    }

    public function render()
    {
        $this->_renderShiftHeader();
        $this->clockIn->render();
        $this->_renderStamps();
        $this->clockOut->render();
        $this->renderSeparaterRow();
        $this->_renderClosingTag();
    }

    private function renderSeparaterRow()
    {
        // echo '<div class="flex-row"></div>';
    }

    public function renderShiftContainer()
    {
        echo '<div class="flex-col flex-start pr-shift-header" id="ui:payroll:shiftRow" data-employeeId="' . $this->employee_id . '" data-shiftId="' . $this->id . '">';
    }

    public function renderCloseShiftContainer()
    {
        echo '</div>';
    }

    private function _renderClosingTag()
    {
        echo '</div>';
    }

    private function _getShiftTotal()
    {
        $accum = $this->clockOut->getDuration();
        foreach ($this->stamps as $stamp) {
            $curr = $stamp->getModifiableDurations();
            $accum += $curr;
        }
        return number_format($accum, 2, '.', '');
    }

    private function _renderShiftHeader()
    {
        return view('Domain.Payroll.shift_header', $this->_shiftDetails());
    }

    private function _shiftDetails()
    {
        $shift = [];
        $shift['shift_id'] = $this->id;
        $shift['shift_date'] = $this->clockIn->getDate();
        $shift['shift_total'] = $this->_getShiftTotal();
        $shift['employee_id'] = $this->employee_id;
        return $shift;
    }

    private function _renderStamps()
    {
        foreach ($this->stamps as $stamp) {
            $stamp->render();
        }
    }

    private function _init()
    {
        $this->_getActions();
    }

    private function _getActions()
    {
        $this->clockIn = $this->_getClockIn();
        $this->clockOut = $this->_getClockOut();
        $this->stamps = $this->_getStamps();
    }

    public function getClockInUnixTs()
    {
        return $this->clockIn->getUnixTs();
    }
    public function getClockInId()
    {
        return $this->clockIn->getId();
    }
    public function setClockInRelatedTo($clockOutId)
    {
        $this->clockIn->setRelatedTo($clockOutId);
    }
    private function _getClockIn()
    {
        $clockIn = new Clockin(Timesheet::where('shift_id', $this->id)->andWhere('activity_id', 1)->get());
        $clockIn->init();
        return $clockIn;
    }
    private function _getClockOut()
    {
        $clockOutAction = Timesheet::where('shift_id', $this->id)->andWhere('activity_id', 0)->get();
        if (empty($clockOutAction)) {
            $clockOutAction = $this->_openStampEnd();
        } else {
            $clockOutAction['state'] = true;
        }

        $clockOut = new Clockout($clockOutAction);
        $clockOut->Shift = $this;
        $clockOut->init();
        return $clockOut;
    }
    private function _getStamps()
    {
        return $this->_createActionStamps($this->_createStampPairs());
    }

    private function _createActionStamps($arrayOfStampPairs)
    {
        $c = [];
        foreach ($arrayOfStampPairs as $pair) {
            $stamp = new Stamp($pair);
            $stamp->init();
            $c[] = $stamp;
        }
        return $c;
    }

    private function _createStampPairs()
    {
        $stamps = $this->_loadStamps() ?? [];
        return $this->_extractStampPairs($stamps);
    }

    private function _extractStampPairs($stamps)
    {
        $c = []; // container array

        // loop, shift off the first element
        while (count($stamps) > 0) {
            $stamps = Arr::retMulti($stamps);
            $first = array_shift($stamps);
            if (empty($stamps)) { // if shifting the array empties it, end the loop and return the default array
                $next = $this->_openStampEnd();
                $first['editable'] = ''; // set editable to null
                $c[] = [$first, $next];
                break;
            } else {
                // otherwise, check if inverse actions, shift array again to get the 'next' element and push to container
                if ($this->_isInverse($first, $stamps)) {
                    $next = array_shift($stamps);
                    $first['state'] = true;
                    $next['state'] = true;
                    $c[] = [$first, $next];
                }
            }
        }
        return $c;
    }

    private function _openStampEnd()
    {
        return [
            'time_stamp' => null,
            'unix_ts' => time(),
            'editable' => '',
            'state' => false,
            'id' => '',
        ];
    }
    private function _isInverse($stamp, $arrayOfStamps)
    {
        return $stamp['activity_id'] = - (current($arrayOfStamps)['activity_id']);
    }

    private function _loadStamps()
    {
        return Timesheet::whereNotIn('activity_id', [0, 1])->andWhere('shift_id', $this->id)->get();
    }

    public function getStartDow()
    {
        return date('w', strtotime($this->clockIn->getDate()));
    }
}
