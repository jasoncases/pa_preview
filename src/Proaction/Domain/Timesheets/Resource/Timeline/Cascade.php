<?php

namespace Proaction\Domain\Timesheets\Resource\Timeline;

use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\System\Resource\Helpers\Arr;

class Cascade
{
    protected $_shifts = [];
    protected $_renders = [];
    //
    public function __construct($shifts)
    {
        // echo '<pre>';
        $this->_shifts = $shifts;
    }

    public function render()
    {
        $this->_createRenders();
        $this->_renderHeader();
        foreach ($this->_renders as $shift) {
            $shift->render();
        }
    }

    private function _renderHeader()
    {
        echo '<div class="cascade-message pd-1x" id="cascade-row">' .
            $this->_getShiftDate() . '</div>';
    }

    private function _getShiftDate()
    {
        $first = $this->_shifts[0] ?? $this->_shifts;
        return date('Y-m-d', strtotime($first['created_at']));
    }

    private function _createNewShift($shift)
    {
        $shift->Cascade = $this;
        return $shift;
    }

    private function _getShiftData(int $id)
    {
        return Timesheet::where('shift_id', $id)->get();
    }

    private function _createRenders()
    {
        foreach ($this->_shifts as $shift) {
            $this->_renders[] = $this->_createNewShift(new Shift($this->_getShiftData($shift->id)));
        }
    }
}
