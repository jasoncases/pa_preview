<?php

namespace Proaction\Domain\Timesheets\Resource;

use Proaction\Domain\Clients\Resource\ProactionClient;
use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\Domain\Timesheets\Model\TimesheetCommentChain;
use Proaction\Domain\Timesheets\Service\ShiftCloser;
use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\System\Resource\Cache\Cache;
use Proaction\System\Resource\Cache\ProactionRedis;

class Action
{

    /**
     * Record a timesheet punch action for an employee
     *
     * @param integer $employee_id  -
     * @param integer $activity_id  - maps to worktype_core activity ids
     * @param integer $shift_id
     * @param integer $advance      - In edge cases we need to advance
     *                                the time stamp to avoid conflicts
     *                                This value takes seconds as arg
     * @param string $activity_flag - reviewed/unreviewed
     * @return boolean
     */
    public function punch(int $employee_id, int $activity_id, int $shift_id, $advance = 0, string $activity_flag = 'null')
    {
        if (gettype($advance) == 'string') {
            $activity_flag = $advance;
            $advance = 0;
        }
        $time_stamp = date('Y-m-d H:i:s');
        $unix_ts = strtotime($time_stamp) + $advance;
        $this->_updateUserSessionOnClockOut($employee_id);
        return $this->_saveClockPunch(
            $employee_id,
            compact(
                'employee_id',
                'shift_id',
                'time_stamp',
                'unix_ts',
                'activity_flag',
                'activity_id'
            )
        );
    }

    private function _updateUserSessionOnClockOut($employee_id)
    {
        // $user = ProactionUser::getInstance();
        // $user->clockout($employee_id);
    }

    private function _saveClockPunch($employee_id, array $clockPunchValues)
    {
        if (Timesheet::store($clockPunchValues)) {
            $this->_updateCache($employee_id);
            // This method MUST return true to finish closing an emp's
            // shift. Turning a shift inactive and finalizing the time
            // data is run after the punch, back in TimesheetAction
            return true;
        }
    }

    private function _updateCache($employee_id)
    {
        $cache = new Cache(ProactionClient::prefix(), ProactionRedis::getInstance());
        $cache->updateUser($employee_id);
    }

    public function forced(int $employee_id, int $activity_id, int $shift_id, int $unix_ts)
    {
        $time_stamp = date('Y-m-d H:i:s', $unix_ts);
        $activity_flag = 'unreviewed';
        $timesheet_id = $this->_saveClockPunch($employee_id, compact('employee_id', 'shift_id', 'time_stamp', 'unix_ts', 'activity_flag', 'activity_id'));
        $this->_timesheetComment($timesheet_id, $shift_id);
        return $this->_closeShift($shift_id);
    }

    private function _closeShift($shift_id)
    {
        return ShiftCloser::close($shift_id);
    }

    private function _timesheetComment($timesheet_id, $shift_id)
    {
        $comment = 'Proaction: System Auto Clockout';
        return TimesheetCommentChain::p_create(compact('timesheet_id', 'shift_id', 'comment'));
    }
}
