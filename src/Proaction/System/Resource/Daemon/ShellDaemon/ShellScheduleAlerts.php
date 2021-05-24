<?php

namespace Proaction\System\Resource\Daemon\ShellDaemon;

use Proaction\Domain\Clients\Model\GlobalSetting;
use Proaction\Domain\Schedules\Model\ScheduleShift;
use Proaction\System\Resource\Helpers\Arr;

class ShellScheduleAlerts extends ShellAlerts
{
    protected $employees = [];

    private $isLate;

    public function __construct($employeeArray)
    {
        parent::__construct($employeeArray);
    }

    private function _mock()
    {
        $sec = $_GET['pre'] ?? 905;
        return [
            'shift_id' => 89898,
            'first_name' => 'Po',
            'last_name' => 'Boy',
            'employee_id' => 999999,
            'schedule_id' => 144444,
            'timestamp_start' => date('Y-m-d H:i:s', time() - $sec),
            'timestamp_end' => date('Y-m-d H:i:s', time() + 3600 * 6),
            'preceding' => $sec,
            'status' => $_GET['rem'] ? 'clockedin' : 'clockedout',
        ];
    }

    protected function _processAlerts()
    {
        if (isset($_GET['pre'])) {
            $this->employees[] = $this->_mock();
        }

        foreach ($this->employees as $emp) {
            if ($emp['status'] == 'clockedout') {
                $this->_processTardyAlerts($emp);
            } else {
                $this->_processWrapupAlerts($emp);
            }
        }
    }

    /**
     * Process tardy alerts for employees that have yet to clock in aga-
     * inst their scheduled in time
     *
     * @param array $emp
     * @return void
     */
    private function _processTardyAlerts($emp)
    {
        extract($emp);
        $this->isLate = time() > strtotime($timestamp_start);
        // if emp is not late, guard out
        if (!$this->isLate) {
            return;
        }
        Arr::pre($emp);
        // check that we are past the point of the second buffer being
        // played. If a user clocks out mid-shift, the system may reco-
        // gnize that as late and play the alerts again
        $bufferPassed = (int) GlobalSetting::get('tardy_alert_email') + $this->buffer;
        if (time() - strtotime($timestamp_start) > $bufferPassed) {
            return;
        }

        // process tardy alerts
        $this->_tardyWarning($emp);
        $this->_tardyNotice($emp);
    }

    /**
     * Anatomy of an alert. All alerts should follow a similar pattern
     *
     * warning - plays a voice announcement, telling the employee to cl-
     *           ock in
     *
     * notice - plays another voice announcement, and sends management
     *          an email alert
     *
     * @param array $emp
     * @return void
     */
    private function _tardyWarning($emp)
    {
        extract($emp);
        // get the time between now and the scheduled in-time
        // we already guarded out anyone who isn't late, so this will
        // always be a positive duration
        $lateTimer = time() - strtotime($timestamp_start);
        // get the target time from the settings model
        $tardy_warning = (int) GlobalSetting::get('tardy_alert_voice');
        // compare, if timer is greater than target, play alert
        // the cron job that builds this state runs every minute, so
        // if we are more than 1 cycle from target, don't do anything.
        // alternative would be to create guard clause w/ the second
        // statement
        if ($lateTimer >= $tardy_warning && $lateTimer < $tardy_warning + $this->buffer) {
            $this->_playAlert('shift', 'shift_tardy_warning', $employee_id);
        }
    }

    private function _tardyNotice($emp)
    {
        extract($emp);
        $lateTimer = time() - strtotime($timestamp_start);
        $tardy_notice = (int) GlobalSetting::get('tardy_alert_email');
        if ($lateTimer >= $tardy_notice && $lateTimer < $tardy_notice + $this->buffer) {
            $this->_playAlert('shift', 'shift_tardy_notice', $employee_id);
        }
    }

    /**
     * Standalone wrap up alert to tell the employee that their shift is
     * over in 2700s (45m)
     *
     * @param array $emp
     * @return void
     */
    private function _processWrapupAlerts($emp)
    {
        extract($emp);
        $target = (int) GlobalSetting::get('shift_wrapup_threshold');
        $remaining = $this->_getRemainingTime($employee_id);
        // if remaining not found, there is no scheduled time. Bounce
        if (!$remaining) {
            return;
        }
        // using default values, if remaining is between the range of
        // 2700s and 2760s, play the alert
        if ($remaining <= $target + $this->buffer && $remaining > $target) {
            $this->_playAlert('shift', 'shift_wrapup', $employee_id);
        }
    }

    private function _getRemainingTime($employee_id)
    {
        return ScheduleShift::where('employee_id', $employee_id)
            ->where('DATE(timestamp_end)', date('Y-m-d'))
            ->oldest('id')
            ->get('UNIX_TIMESTAMP(timestamp_end) - UNIX_TIMESTAMP() as remaining');
    }
}
