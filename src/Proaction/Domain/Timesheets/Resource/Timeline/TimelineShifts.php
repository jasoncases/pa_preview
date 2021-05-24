<?php

namespace Proaction\Domain\Timesheets\Resource\Timeline;

use Proaction\Domain\Timesheets\Model\Shift as ShiftModel;
use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\System\Database\CDB;
use Proaction\System\Resource\Helpers\Arr;

class TimelineShifts
{

    protected $shifts = [];

    public function __construct($employee_id, $date)
    {
        $this->_getShifts($employee_id, $date);
    }

    public function getOutput()
    {
        $c = [];
        foreach ($this->shifts as $shift) {
            $c[] = $shift->getShiftOutput();
        }

        return $c;
    }

    public function getMinAction()
    {
        if (!empty($this->shifts)) {
            $c = [];
            foreach ($this->shifts as $shift) {
                $c[] = $shift->getMinAction();
            }
            return min($c);
        }
        return null;
    }

    public function getMaxAction()
    {
        if (!empty($this->shifts)) {
            $c = [];
            foreach ($this->shifts as $shift) {
                $c[] = $shift->getMaxAction();
            }
            return max($c);
        }
        return null;
    }


    private function _getShifts($employee_id, $date)
    {
        $shifts = ShiftModel::whereRaw("DATE(created_at) = ?", [$date])->where('employee_id', $employee_id)->get();
        foreach ($shifts as $shift) {
            $tlShift = new TimelineShift($this->_stampsByShiftId($shift->id));
            $tlShift->shift_creation_date = $shift->created_at;
            $this->shifts[] = $tlShift;
        }
    }

    private function _stampsByShiftId($id)
    {
        return Timesheet::where('shift_id', $id)
            ->leftJoin('worktype_core as b', 'b.id', 'ts_timesheet.activity_id')
            ->oldest('id')
            ->get(
                [
                    'ts_timesheet.id',
                    'ts_timesheet.shift_id as shiftId',
                    'ts_timesheet.time_stamp as stamp',
                    'ts_timesheet.activity_id as activityId',
                    'ts_timesheet.unix_ts as unixTs',
                    'b.bar_color as barColor',
                    'b.identifier',
                ]
            );
    }
}
