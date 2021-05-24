<?php

namespace Proaction\Domain\Timesheets\Model;

use Proaction\Domain\Clients\Model\GlobalSetting;
use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Timesheets\Resource\IpActionLog;
use Proaction\System\Database\CDB;
use Proaction\System\Model\ClientModel;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Helpers\Misc;

/**
 *
 */
class Timesheet extends ClientModel
{
    //
    protected $table = 'ts_timesheet';

    protected $autoColumns = ['edited_by'];

    public static function getNextTimestamp(Timesheet $t)
    {
        // if the action is a clockout, need to get the clock in of the
        // next shift
        if ($t->activity_id == 0) {
            $s = Shift::getNextShiftByShiftId($t->shift_id);
            return self::where('shift_id', $s->id)->where('activity_id', 1)->first();
        }
        // otherwise return the next timesheet id for the given emp id
        return self::whereRaw('id = (SELECT MIN(id) from ts_timesheet WHERE id > ? AND employee_id = ?)', [$t->id, $t->employee_id])
            ->first();
    }

    public static function getPrevTimestamp(Timesheet $t)
    {
        // if the action is a clockin, need to get the clock out of the
        // next shift
        if ($t->activity_id == 0) {
            $s = Shift::getPrevShiftByShiftId($t->shift_id);
            return self::where('shift_id', $s->id)->where('activity_id', 0)->first();
        }
        // otherwise return the prev timesheet id for the given emp id
        return self::whereRaw('id = (SELECT MAX(id) from ts_timesheet WHERE id < ? AND employee_id = ?)', [$t->id, $t->employee_id])
            ->first();
    }

    public static function getCurrentShiftBreakElapsedTime($id)
    {
        if (!Employee::isClockedIn($id)) {
            return 0;
        }
        $shift = Shift::currentByEmployeeId($id);
        if (!$shift) {
            return 0;
        }

        return self::getBreakTimeByShiftId($shift->id);
    }

    public static function getCurrentShiftLunchElapsedTime($id)
    {
        if (!Employee::isClockedIn($id)) {
            return 0;
        }
        $shift_id = Shift::currentByEmployeeId($id);
        if (!$shift_id) {
            return 0;
        }
        return self::getLunchTimeByShiftId($shift_id);
    }

    public static function getBreakTimeByShiftId($shift_id)
    {
        return Misc::money(self::getBreakTimeInSecondsByShiftId($shift_id) / 3600);
    }

    public static function getBreakTimeInSecondsByShiftId($shift_id)
    {
        $b = [3, -3];
        $stamps = self::whereIn('activity_id', $b)
            ->where('shift_id', $shift_id)
            ->oldest('unix_ts')
            ->get('unix_ts');
        if ($stamps->isEmpty()) {
            return 0;
        }
        return self::_accumulateStamps($stamps);
    }

    public static function getLunchTimeInSecondsByShiftId($shift_id)
    {
        $b = [5, -5];
        $stamps = self::whereIn('activity_id', $b)
            ->where('shift_id', $shift_id)
            ->oldest('unix_ts')
            ->get('unix_ts');
        if ($stamps->isEmpty()) {
            return 0;
        }
        return self::_accumulateStamps($stamps);
    }

    public static function getLunchTimeByShiftId($shift_id)
    {
        return Misc::money(self::getLunchTimeInSecondsByShiftId($shift_id) / 3600);
    }

    public static function test_acc($stamps)
    {
        return self::_accumulateStamps($stamps);
    }

    private static function _accumulateStamps($stamps)
    {
        $stamps = $stamps->sortBy('unix_ts')->pluck('unix_ts')->toArray();
        if (count($stamps) % 2) {
            $stamps[] = time();
        }
        $acc = 0;
        for ($ii = 0; $ii < count($stamps); $ii++) {
            $val = intval($stamps[$ii]);
            if ($ii % 2) {
                $acc = $acc + $val;
            } else {
                $acc = $acc - $val;
            }
        }
        return $acc;
    }

    public static function getClockInTimestamp($id)
    {
        if (Employee::isClockedIn($id)) {
            return self::getLastTimestampByAction($id, 1);
        }
    }

    public static function getLunchOutTimestamp($id)
    {
        if (Employee::isClockedIn($id)) {
            return self::getLastTimestampByAction($id, 5);
        }
    }

    public static function getLastTimestampByAction($employeeId, $actionId)
    {
        return Timesheet::where('employee_id', $employeeId)
            ->where('activity_id', $actionId)
            ->latest('id')
            ->limit(1)
            ->first('unix_ts')->unix_ts;
    }

    public function shift()
    {
        $this->hasOne(`\Model\Shift\\`);
    }

    public static function store($punch)
    {
        $action = self::p_create($punch);
        return (new IpActionLog(
            self::ipSnapshot($action->id)
        ))->do();
    }

    public static function ipSnapshot($timesheet_id)
    {
        if (is_array($timesheet_id) || is_object($timesheet_id)) {
            throw new \Exception('Timesheet::isSnapshot($timesheet_id) - provided argument MUST BE an integer');
        }
        return self::where('ts_timesheet.id', $timesheet_id)
            ->leftJoin('employee_view as b', 'b.id', 'employee_id')
            ->leftJoin('worktype_core as c', 'c.actionId', 'activity_id')
            ->first(
                [
                    'ts_timesheet.id',
                    'ts_timesheet.time_stamp',
                    'b.fullDisplayName as employee_name',
                    'c.text as punch_type',
                    'ts_timesheet.activity_id',
                    // this will always return an empty string, so we need to
                    // catch and process correctly in IpActionLog
                    CDB::raw('"' . $_SERVER['REMOTE_ADDR'] . '" as _ip'),
                ]
            );
    }

    public static function getMostRecentActivity($id, $range)
    {
        return (new static)->_getMostRecentActivity($id, $range);
    }

    private function _getMostRecentActivity($id, $range)
    {
        $actions = Timesheet::where('ts_timesheet.employee_id', $id)
            ->leftjoin('worktype_core as b', 'b.actionId', 'ts_timesheet.activity_id')
            ->latest('ts_timesheet.id')
            ->limit($range)
            ->get([
                'ts_timesheet.activity_id',
                'b.text',
                'ts_timesheet.id',
                CDB::raw('DATE_FORMAT(DATE(ts_timesheet.time_stamp), "%b-%d") as date'),
                CDB::raw('TIME(ts_timesheet.time_stamp) as time'),
            ]);
        return Arr::sort($actions, 'id');
    }

    /**
     * Return all timesheet actions for given shift id value
     *
     * @param integer $shift_id
     * @return array|null
     */
    public static function getShift(int $shift_id)
    {
        return self::where('shift_id', $shift_id)->oldest('id')->get();
    }

    /**
     * Get elapsed time of shift lunch break
     *
     * @param integer $shift_id
     * @return int
     */
    public static function getCurrentShiftLunchElapsed($shift_id)
    {
        $lunch = Timesheet::whereIn('activity_id', [5, -5])->where('shift_id', $shift_id)->get();
        $unix = $lunch->pluck('unix_ts')->toArray();
        if ($lunch->count() > 1) {
            $start = current($unix);
            $end = $unix[1] ?? time();
            // $unix = array_column($lunch);
            return abs($end - $start);
        }
        return 0;
    }

    /**
     * Return elapsed time of open shift in seconds
     *
     * @param integer $shift_id
     * @return int
     */
    public static function getCurrentShiftElapsed(int $shift_id)
    {
        $clockIn = Timesheet::where('shift_id', $shift_id)->where('activity_id', 1)->first();
        return time() - $clockIn->unix_ts;
    }

    /**
     * Return elapsed length of current shift less any lunch break time
     *
     * @param integer $id
     * @return void
     */
    public static function getCurrentShiftLength(int $id)
    {
        $shift_id = Employee::getCurrentShiftId($id);
        return Timesheet::getCurrentShiftElapsed($shift_id)
            - Timesheet::getCurrentShiftLunchElapsed($shift_id);
    }

    /**
     * Return an array of all timesheet data, for the last activity, by
     * employee id
     *
     * @param int $id
     * @return Timesheet
     */
    public static function getLastEmployeeActivity(int $id)
    {
        $shift = Shift::getLastEmployeeActivity($id);
        // if there is not an active shift...
        if ($shift) {
            return self::where('employee_id', $id)
                ->where('shift_id', $shift->id)
                ->latest('id')
                ->limit(1)
                ->first();
        }
        // ... return last clockout as last activity
        return self::getLastClockout($id);
    }

    public static function getLastClockout($id)
    {
        $shift = Shift::where('employee_id', $id)->latest('id')->limit(1)->first();
        if (boolval($shift)) {
            return self::where('shift_id', $shift->id)->where('activity_id', 0)->first();
        } else {
            return self::nullClockOut();
        }
    }

    private static function nullClockOut()
    {
        $shift = new Shift;
        $shift->activity_id = 0;
        $shift->id = 99999999999999;
        $shift->unix_ts = strtotime("-1 days");
        $shift->time_stamp = date('Y-m-d', $shift->unix_ts);
        $shift->devNote = 'This is a null stamp. As a new user, there is no prior shift';
        return $shift;
    }
    /**
     * Return the start of the shift in UNIX seconds
     *
     * @param integer $shift_id
     * @return int
     */
    public static function getShiftStartUnix(int $shift_id)
    {
        $shift = self::where('shift_id', $shift_id)
            ->where('activity_id', 1)
            ->first('unix_ts');
        return boolval($shift) ? $shift->unix_ts : 0;
    }

    /**
     * Returns a formatted array for sending Timeclock actions to the
     * running Daemon process
     *
     * @param array $shift
     * @return array
     */
    public static function timeclockFormat(Shift $shift)
    {
        return (new static)->_timeclockFormat($shift);
    }

    /**
     * Return all timesheet actions for a given employee id and supplied
     * date. Defaults to current date, if none given
     *
     * @param integer $id
     * @param string $date
     * @return void
     */
    public static function getActionsByDate(int $id, $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        return Timesheet::where('DATE(time_stamp)', $date)->where('employee_id', $id)->get() ?? [];
    }

    public static function getActionsByShiftId($employeeId, $shift_id = null)
    {

        if (is_null($shift_id)) {
            $shift_id = $employeeId;
        }

        return self::where('shift_id', $shift_id)->get();
    }

    public static function getBreakStatus($id)
    {
        $maxBreak = GlobalSetting::get('max_break_count');
        $maxLunch = GlobalSetting::get('max_lunch_count');
        $breakCount = self::getCurrentBreakCount($id);
        $lunchCount = self::getCurrentLunchCount($id);
        return [
            'breakStatus' => $breakCount >= $maxBreak ? 'disabled' : 'enabled',
            'lunchStatus' => $lunchCount >= $maxLunch ? 'disabled' : 'enabled',
            'breakCount' => $breakCount,
            'lunchCount' => $lunchCount,
        ];
    }

    /**
     * Return the number of breaks on the current shift, by employee id
     *
     * @param integer $id
     * @return int
     */
    public static function getCurrentBreakCount(int $id, $shift_id = null)
    {
        if (!Employee::isClockedIn($id)) {
            return 0;
        }

        if (is_null($shift_id)) {
            $shift_id = Shift::currentByEmployeeId($id);
            if (is_null($shift_id)) {
                return 0;
            }
        }

        $allActions = Timesheet::getActionsByShiftId($id, $shift_id);

        $activityIds = array_count_values(array_column($allActions->toArray(), 'activity_id'));
        return $activityIds[3] ?? 0;
    }

    /**
     * Return the number of lunch punches on the current shift, by
     * employee id
     *
     * @param integer $id
     * @return int
     */
    public static function getCurrentLunchCount(int $id, $shift_id = null)
    {
        if (is_null($shift_id)) {
            $shift_id = Shift::currentByEmployeeId($id);
            if (is_null($shift_id)) {
                return 0;
            }
        }
        $allActions = Timesheet::getActionsByShiftId($id, $shift_id);
        $activityIds = array_count_values(array_column($allActions->toArray(), 'activity_id'));
        return $activityIds[5] ?? 0;
    }

    /**
     * Format and return employees and their Timesheet states
     */
    public static function getOnBreak()
    {
        return (new static)->_getEmployeesOnBreak();
    }

    /**
     * Private method matching a public-static wrapper
     * Format and return employees and their Timesheet states
     *
     * @param array $shift
     * @return array
     */
    private function _getEmployeesOnBreak()
    {
        // get active employees
        $emps = Employee::getActiveEmployees(['first_name', 'nickname', 'id']);
        $c = [];

        // foreach check if they are on break
        foreach ($emps as $emp) {
            $onBreak = (array) Employee::isOnBreak($emp['id']);
            if ($onBreak) {
                $c[] = array_merge($emp, $onBreak);
            }
        }

        // sort to float break state emps to the top
        if (count($c) > 1) {
            $time = array_column($c, 'duration');
            $status = array_column($c, 'activity_id');
            array_multisort($status, SORT_ASC, $time, SORT_DESC, $c);
        }

        // return the container array
        return $c;
    }

    /**
     * Private method matching a public-static wrapper
     * Format for Daemon process
     *
     * @param array $shift
     * @return array
     */
    private function _timeclockFormat(Shift $shift)
    {
        $actions = $this->_getActionsForDaemonByShiftId($shift->id);
        return $this->_formatActionsForDaemon($actions);
    }

    /**
     *
     *
     * @param integer $shift_id
     * @return object
     */
    private function _getActionsForDaemonByShiftId(int $shift_id)
    {
        return self::where('shift_id', $shift_id)
            ->oldest('unix_ts')
            ->get(
                [
                    'employee_id as employeeId',
                    'shift_id as shiftId',
                    'unix_ts as unixTime',
                    'activity_id as activityId'
                ]
            );
    }

    /**
     *
     *
     * @param array $actions
     * @return void
     */
    private function _formatActionsForDaemon($actions)
    {
        $c = [];
        foreach ($actions as $action) {
            $c[] = [
                'employeeId' => $action->employeeId,
                'data' => [
                    'shiftId' => $action->shiftId,
                    'activityId' => $action->activityId,
                    'unixTime' => $action->unixTime
                ],
            ];
        }
        return $c;
    }

    public static function getTimesheetDetailByShiftId($shift_id)
    {
        return self::where('ts_timesheet.shift_id', $shift_id)
            ->leftJoin('employee_view as b', 'b.id', 'employee_id')
            ->leftJoin('ts_shifts as c', 'c.id', 'shift_id')
            ->get(
                [
                    'ts_timesheet.shift_id',
                    'ts_timesheet.unix_ts',
                    'b.first_name',
                    'b.last_name',
                    'ts_timesheet.employee_id',
                    'ts_timesheet.activity_id',
                    'c.pay_rate as _rate'
                ]
            );
    }
}
