<?php

namespace Proaction\Domain\Timesheets\Resource\Timeline;

use Proaction\Domain\Worktypes\Model\Worktype;
use Proaction\System\Resource\Helpers\Arr;

class Couplet
{
    // Class Pointers
    public $Shift;

    // Props
    protected $_start;
    protected $_end;
    protected $_data = ['actionName' => '', 'startPunch' => '', 'endPunch' => '', 'duration' => ''];
    protected $_detract = 1;
    protected $_isClockIn = false;
    protected $_isClockOut = false;
    protected $_isOpen = false;

    public function __construct($start,  $end)
    {
        $this->_start = $start;
        $this->_end = $end;
    }

    public function init()
    {
        $this->_init();
    }

    private function _init()
    {
        $this->_setDetractState();
        $this->_setState();
        $this->_confirmCompatibleActions();
        $this->_setDuration();
        $this->_setNextDay();
        $this->_formatStartAndEndForOutput();
    }

    private function _confirmCompatibleActions()
    {
        /**
         *  in the case of open shifts, ::_end is null, so assume true
         */
        if (!$this->_end) {
            return true;
        }

        if (!$this->_isClockIn && !$this->_isClockOut) {
            if ($this->_start->activity_id != -$this->_end->activity_id) {
                $this->_isOpen = true;
            }
        }
    }

    private function _setState()
    {
        if (is_object($this->_start)) {
            if ($this->_start->activity_id == 1) {
                $this->_isClockIn = true;
                $this->_isOpen = true;
            }
        } else {
            if (is_object($this->_end)) {
                if ($this->_end->activity_id == 0) {
                    $this->_isClockOut = true;
                }
            }
        }
    }

    private function _setActionName()
    {
        $activityId = empty($this->_start) ? $this->_end->activity_id : $this->_start->activity_id;
        $this->_data['actionName'] = Worktype::where('actionId', $activityId)->first()->text;
    }

    private function _formatStartAndEndForOutput()
    {
        $this->_setActionName();
        $this->_setStartPunch();
        $this->_setEndPunch();
    }

    private function _setStartPunch()
    {
        if (is_object($this->_start)) {
            $this->_data['startPunch'] = date('H:i:s', strtotime($this->_start->time_stamp));
        }
    }

    private function _setEndPunch()
    {
        if (is_object($this->_end)) {
            // is_null() doesn't work here.
            $endStamp = $this->_end->time_stamp == null ? '' : date('H:i:s', strtotime($this->_end->time_stamp));
            $this->_data['endPunch'] = $endStamp;
        }
        // ! I think this is a bogus edgecase. Haven't seen it the wild
        // else {
        //     if (!empty($this->_start) && $this->_start->activity_id != 1) {
        //         // $this->_data['duration'] = '';
        //     }
        // }
    }

    private function _setDetractState()
    {
        if (is_object($this->_start)) {
            if ($this->_start->activity_id == 5) {
                $this->_detract = -1;
            }
        }
    }

    private function _renderRow(array $data)
    {
        extract($data);
        $duration = $duration == 0 ? '' : number_format(abs($duration), 2, '.', '');
        return '<div class="flex-row space-between" id="cascade-row">
                    <div class="flex-col flex-center pdh-2x pdv-1x text-left flex-2">' . $actionName . '</div>
                    <div class="flex-col flex-center pdh-2x pdv-1x text-center flex-1">' . $startPunch . '</div>
                    <div class="flex-row flex-center pdh-2x pdv-1x text-center flex-1">' . $endPunch . ' ' . $next_day . '</div>
                    <div class="flex-col flex-center pdh-2x pdv-1x text-center flex-1">' . $duration . '</div>
                </div>';
    }

    public function render()
    {
        echo $this->_renderRow($this->_data);
    }

    private function _setDuration()
    {
        $this->_data['duration'] = is_null($this->getDuration()) ? ' ' : number_format(round($this->getDuration() / 3600, 2), 2, '.', '');
    }

    public function getDuration()
    {
        return $this->_getUnixDuration();
    }

    private function _getUnixDuration()
    {
        if ($this->_isClockOut) {
            return $this->_getFullShiftDuration();
        } else if (!$this->_isClockIn) {
            if ($this->_isOpen) {
                return $this->_getOpenDuration();
            }
            return $this->_calcCoupletDuration();
        }
        return 0;
    }

    private function _getOpenDuration()
    {
        $start = $this->_start->unix_ts;
        $end = time();
        return $this->_detract * ($end - $start);
    }

    private function _calcCoupletDuration()
    {
        $start = $this->_start->unix_ts;
        $end = $this->_end->unix_ts ?? time();
        return $this->_detract * ($end - $start);
    }

    private function _getFullShiftDuration()
    {
        // null state produces error [ticket #125]
        // [http://jasoncases.zerodock.com/tickets/125]
        if (is_null($this->_pullShiftStart())) {
            return;
        }
        $startDur = $this->_pullShiftStart()->getStartUnixTimestamp();
        return $this->_end->unix_ts - $startDur;
    }

    private function _pullShiftStart()
    {
        return $this->Shift->getClockInCouplet();
    }

    public function isClockIn()
    {
        return $this->_isClockIn;
    }
    public function isClockOut()
    {
        return $this->_isClockOut;
    }

    public function getStartUnixTimestamp()
    {
        return $this->_start->unix_ts;
    }

    public function getUnixDuration()
    {
        return $this->_end->unix_ts - $this->_start->unix_ts;
    }

    public function isDetract()
    {
        return $this->_detract == -1;
    }

    private function _setNextDay()
    {
        $this->_data['next_day'] = '';
        if (!is_null($this->getDuration())) {
            if ($this->isClockOut()) {
                $clockin = $this->Shift->getClockInCouplet();
                $clockinDate = date('Y-m-d', $clockin->getStartUnixTimestamp());
                $clockoutDate = date('Y-m-d', $this->_end->unix_ts);;
                if ($clockinDate != $clockoutDate) {
                    $this->_data['next_day'] =  '<span class="text-danger mgl-1x">(*)</span>';
                }
            }
        }
    }
}
