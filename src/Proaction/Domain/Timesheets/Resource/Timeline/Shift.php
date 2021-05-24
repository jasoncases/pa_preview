<?php

namespace Proaction\Domain\Timesheets\Resource\Timeline;

use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\System\Resource\Helpers\Arr;

class Shift
{
    // Class Pointers
    public $Cascade;

    // Props
    protected $_actions = [];
    protected $_renders = [];
    protected $_data = [];

    public function __construct($shiftActions)
    {
        $this->_actions = $shiftActions;
        // echo '<pre>';
        // print_r($this->_actions);
        $this->_init();
    }

    private function _init()
    {
        $this->_checkForSingleActionEdgeCase();
        $this->_checkForOpenShift();
        $this->_sortActions();
        $this->_getMatchingCoupletActions();
        $this->_setDate();
        $this->_setId();
        $this->_setDuration();
    }

    private function _setId()
    {
        $this->_data['shift_id'] = $this->_actions->first()->shift_id;
    }

    private function _checkForOpenShift()
    {
        $lastAction = $this->_actions->last();
        if ($lastAction->activity_id != 0) {
            $this->_actions[] = $this->_injectDummyCloseAction();
        }
    }
    private function _checkForSingleActionEdgeCase()
    {
        if (!isset($this->_actions[0])) {
            $this->_actions = [$this->_actions, $this->_injectDummyCloseAction()];
        }
    }

    private function _injectDummyCloseAction()
    {
        $mock = new Timesheet;
        $mock->activity_id = 0;
        $mock->date = date('Y-m-d');
        $mock->unix_ts = time();
        $mock->text = 'Clock Out';
        return $mock;
    }

    private function _setDate()
    {
        $this->_data['date'] = $this->_retDate($this->_actions->first()->time_stamp);
    }

    private function _retDate($timestamp): string
    {
        return date('Y-m-d', strtotime($timestamp));
    }

    public function render()
    {
        $this->_renderHeader();
        $this->_renderRenders();
        $this->_renderFooter();
    }

    private function _createCouplet($couplet)
    {
        $couplet->Shift = $this;
        $couplet->init();
        return $couplet;
    }

    private function _getMatchingCoupletActions()
    {
        $len = count($this->_actions);
        for ($ii = 0; $ii < $len; $ii++) {
            $currAction = $this->_actions[$ii];
            $nextAction = $this->_actions[$ii + 1] ?? null;
            $this->_isACouplet($currAction, $nextAction);
        }

        // print_r($this->_renders);
    }

    private function _pushToRenders($couplet)
    {
        $this->_renders[] = $couplet;
        return true;
    }

    private function _isACouplet($currAction, $nextAction)
    {
        if ($currAction->activity_id == 1) {
            return $this->_pushToRenders($this->_createCouplet(new Couplet($currAction, null)));
        } else if ($currAction->activity_id == 0) {
            return $this->_pushToRenders($this->_createCouplet(new Couplet(null, $currAction)));
        } else if ($currAction->activity_id == - ($nextAction->activity_id) && $currAction->activity_id > 1) {
            return $this->_pushToRenders($this->_createCouplet(new Couplet($currAction, $nextAction)));
        } else if ($currAction->activity_id > 1 && $nextAction->activity_id != -$currAction->activity_id) {
            return $this->_pushToRenders($this->_createCouplet(new Couplet($currAction, null)));
        }
        return false;
    }

    /**
     * Ensure that the action entries are sorted in the correct OLDEST to NEWEST
     *
     * @return void
     */
    private function _sortActions()
    {
        $this->_actions->sortBy('unix_ts');
    }

    private function _renderHeader()
    {
        //
        echo '<div class="flex-row space-between cascade-header" id="cascade-row">
                <div class="flex-col flex-center pdh-2x pdv-1x text-left flex-2"><span class="text-secondary" style="font-size:12px"></span></div>
                <div class="flex-col flex-center pdh-2x pdv-1x text-center flex-1">Time In</div>
                <div class="flex-col flex-center pdh-2x pdv-1x text-center flex-1">Time Out</div>
                <div class="flex-col flex-center pdh-2x pdv-1x text-center flex-1">Duration</div>
            </div>';
    }

    private function _renderRenders()
    {
        foreach ($this->_renders as $Couplet) {
            $Couplet->render();
        }
    }

    private function _renderFooter()
    {
        $duration = $this->_data['duration'] == 0 ? '' : $this->_data['duration'];
        echo '<div class="flex-row space-between cascade-footer" id="cascade-row">
                <div class="flex-col flex-center pdh-2x pdv-1x text-left flex-2"></div>
                <div class="flex-col flex-center pdh-2x pdv-1x text-center flex-1"></div>
                <div class="flex-col flex-center pdh-2x pdv-1x text-center flex-1"></div>
                <div class="flex-col flex-center pdh-2x pdv-1x text-center flex-1">' . $duration . '</div>
            </div>';
    }

    private function _setDuration()
    {
        $x = $this->_getDuration();
        $this->_data['duration'] = number_format(round($this->_getDuration() / 3600, 2), 2, '.', '');
    }
    private function _getDuration()
    {
        if ($this->getClockOutCouplet()) {
            $fullDuration = $this->getClockOutCouplet()->getDuration();
        } else {
            $fullDuration = time() - $this->getClockInCouplet()->getStartUnixTimestamp();
        }

        return $this->_calcDurationDetractors($fullDuration);
    }

    private function _calcDurationDetractors($duration)
    {
        foreach ($this->_renders as $Couplet) {
            if ($Couplet->isDetract()) {
                $duration += $Couplet->getDuration();
            }
        }
        return $duration;
    }

    public function getClockOutCouplet()
    {
        $end = end($this->_renders);
        return $end->isClockOut() ? end($this->_renders) : null;
    }

    public function getClockInCouplet()
    {
        $start = $this->_renders[0];
        if (is_null($start)) {
            return null;
        }
        return $start->isClockIn() ? $start : null;
    }
}
