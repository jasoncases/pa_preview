<?php

namespace Proaction\Domain\Timesheets\Resource\Timeline;

use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Helpers\Misc;

class TimelineStampCouplet
{

    protected $open, $close;

    public $output;

    public function __construct($open, $close = null)
    {
        $this->open = $open;
        $this->close = $this->_validateClose($close);
        $this->output = $this->_createOutput();
    }

    public function getStart()
    {
        return Misc::stampToFloat($this->open->stamp);
    }

    public function getEnd()
    {
        return $this->_isActive() ? null : Misc::stampToFloat($this->close->stamp);
    }

    private function _isActive()
    {
        return  !isset($this->close->activityId);
    }

    private function _createOutput()
    {
        return [
            // the beginning time of the open stamp
            'start' => floatval(Misc::stampToFloat($this->open->stamp)),
            // the defined color of the action
            'barColor' => $this->open->barColor,
            // the length of current action at rendering
            'length' => floatval($this->_getCoupletLength()),
            // the active state of the couplet. If the close has a
            // defined property, it is set to `not active`
            'active' => $this->_isActive(),
            'action' => $this->_setActionName()
        ];
    }

    private function _setActionName()
    {
        $str = strtolower($this->open->identifier);
        $str = str_replace('-', '', $str);
        $str = str_replace('start', '', $str);
        $str = str_replace('end', '', $str);
        return $str;
    }

    private function _getCoupletLength()
    {
        return round(($this->close->unixTs - $this->open->unixTs) / 3600, 2);
    }

    private function _validateClose($close)
    {
        if (is_null($close) || empty($close)) {
            return $this->_buildNullClose();
        }
        return $close;
    }

    private function _buildNullClose()
    {
        $mock = new Timesheet;
        $mock->unixTs = time();
        return $mock;
    }
}
