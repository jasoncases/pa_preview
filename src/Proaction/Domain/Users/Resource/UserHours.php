<?php

namespace Proaction\Domain\Users\Resource;

use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Payroll\Model\PayrollComplete;
use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\System\Resource\Helpers\Misc;

use function Proaction\System\Helpers\Lib\getPayrollDateRange;

/**
 * Populate the UserStatusCache with employee hours
 */
class UserHours
{
    protected $daily;
    protected $weekly;
    protected $monthly;

    public function __construct($emp)
    {
        $this->id = $emp['employeeId'];
        $this->emp = $emp;
    }

    /**
     *
     * @return array
     */
    public function get()
    {
        return [
            'daily' => $this->_getDailyHours(),
            'weekly' => $this->_getWeeklyHours(),
            'monthly' => $this->_getMonthlyHours(),
        ];
    }

    /**
     * Daily hours are a different shape than weekly and monthly. The
     * idea being, if the user is clocked in and their time is actively
     * changing, so we need to calculate that on the client, so we need
     * to have the data in a format that will be easy for the client to
     * calculate on a set interval. If the user is NOT clocked in, it
     * returns a normal interface
     *
     * @return array
     */
    private function _getDailyHours()
    {
        $isUserClockedIn = Employee::isClockedIn($this->id);
        $lunchStart = Employee::isOnLunch($this->id);
        return [
            'hours' => $isUserClockedIn ? $this->_getShiftProgressHours() : $this->_getSingleDayHours(),
            'isClockedIn' => boolval($isUserClockedIn),
            'clockInTimeStamp' => $isUserClockedIn ? $this->_getClockInStamp() : null,
            'isUserOnLunch' => boolval($lunchStart),
            'lunchOutTimeStamp' => $lunchStart,
        ];
    }

    /**
     * Return the UTC seconds of the user's clock in timestamp
     *
     * @return integer
     */
    private function _getClockInStamp()
    {
        return Timesheet::getClockInTimestamp($this->id);
    }

    /**
     * Returns a partial array of the users shift progress. Returns this
     * only if the user is actively clocked in
     *
     * @return array
     */
    private function _getShiftProgressHours()
    {
        return [
            'totalTimeClockedIn' => null,
            'totalTimeOnBreak' => Timesheet::getCurrentShiftBreakElapsedTime($this->id),
            'totalTimeAtLunch' => Timesheet::getCurrentShiftLunchElapsedTime($this->id),
            'totalTimePaid' => null,
        ];
    }

    /**
     * If user is not actively clocked in, get any hours they may have
     * from any shifts that are in current day
     *
     * @return array
     */
    private function _getSingleDayHours()
    {
        return PayrollComplete::getByRange($this->id, date('Y-m-d'), date('Y-m-d'));
    }

    /**
     * Same return as _getSingleDayHours(), but the date range is the
     * start of the payroll period to the previous day. Previous day is
     * used because no matter what, the user is returned daily hours and
     * those hours are calculated on the client side. So rather than
     * have to deal with additional branches in the execution path, we
     * just do the math on the client regardless
     *
     * @return array
     */
    private function _getWeeklyHours()
    {
        return [
            'hours' =>  PayrollComplete::getByRange(
                $this->id,
                current(Misc::getPayrollDateRange()),
                strtotime("-1 day")
            )
        ];
    }

    /**
     * Same as _getWeeklyHours(), but the start is the first of the
     * current month and the end date is also the previous day, for the
     * same math reasons above
     *
     * @return array
     */
    private function _getMonthlyHours()
    {
        return [
            'hours' => PayrollComplete::getByRange(
                $this->id,
                date('Y-m-01 00:00:00'),
                strtotime("-1 day")
            )
        ];
    }
}
