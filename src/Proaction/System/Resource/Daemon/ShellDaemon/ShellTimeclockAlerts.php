<?php

namespace Proaction\System\Resource\Daemon\ShellDaemon;

use Proaction\Domain\Clients\Model\GlobalSetting;
use Proaction\System\Resource\Daemon\Alerts\Alert;

/**
 * ShellTimeclockAlerts handles all alerts having to do with clocked in
 * employees durations, full-shift length, break and/or lunch duration.
 * Alerts are created and secondary actions can be programmed in the
 * files stored in /Resource/Daemon/Alerts
 */
class ShellTimeclockAlerts extends ShellAlerts
{
    // cron job runs every minute, this buffer allows for a range when
    // break checks are run


    // needed for break/lunch comparison. Values set when needed
    private $break_length;
    private $lunch_length;

    public function __construct($employeeArray)
    {
        parent::__construct($employeeArray);
    }

    private function _mock()
    {
        return [
            "shift_id" => 1111111111,
            "first_name" => 'Poe',
            "last_name" => 'Boiy',
            "employee_id" => 14,
            "action_timestamp" => 1602195952,
            "activity_id" => 5,
            "action_duration" => 3600,
            "status" => 'clockedin',
            "shift_duration" => $_GET['mock'],
            "type" => 'mock',
        ];
    }

    /**
     * Created a container method, so we can process addtional alerts
     * if more circumstances arise
     *
     * @return void
     */
    protected function _processAlerts()
    {
        foreach ($this->employees as $emp) {
            $this->_processTimeclockStateAlerts($emp);
        }
    }

    /**
     * Run all necessary checks for an active employee
     *
     * @param array $emp
     * @return void
     */
    private function _processTimeclockStateAlerts($emp)
    {
        // always happens if clocked in, regardless of micro-state, 135
        $this->_processShiftDurationAlert($emp);
        extract($emp);

        if ($activity_id == 3) {
            echo 'processing break alerts for ' . $emp['employee_id'];
            $this->_processBreakAlerts($emp);
        } else if ($activity_id == 5) {
            echo 'processing lunch alerts for ' . $emp['employee_id'];
            $this->_processLunchAlerts($emp);
        }
    }

    /**
     * Undocumented function
     *
     * @param array $emp
     * @return void
     */
    private function _processShiftDurationAlert($emp)
    {

        // Need to inject a closing state if the user is in a state other
        // than clock, this is better handled at the force_clockout method
        // in the TimesheetAction Resource file
        $maxShiftLength = GlobalSetting::get('unix_auto_clockout');
        if ($emp['shift_duration'] >= $maxShiftLength) {
            $this->_playAlert('clock', 'clockout', $emp['employee_id'], $emp);
        }
    }

    /**
     * Pass the employee array through the break alerts
     *
     * @param array $emp
     * @return void
     */
    private function _processBreakAlerts($emp)
    {
        // set break_length value to be used by all three checks
        $this->break_length = GlobalSetting::get('break_length');
        $this->_breakWrapup($emp);
        $this->_breakAlert($emp);
        $this->_breakNotice($emp);
    }

    /**
     * Same as above, but for lunch rules instead
     *
     * @param array $emp
     * @return void
     */
    private function _processLunchAlerts($emp)
    {
        // set lunch_length value to be used by all three checks
        $this->lunch_length = (int) GlobalSetting::get('lunch_length');
        $this->_lunchWrapup($emp);
        $this->_lunchAlert($emp);
        $this->_lunchNotice($emp);
    }

    /**
     * All checks are performed the same way, compare the target time
     * against the elapsed time. If the elapsed time is between the
     * target and a 60s buffer, send the alert to the server
     *
     * wrapup - default value is 2 minutes, lets the employee know their
     * break is coming to and end
     *
     * alert - default is 0, meaning it plays right at the end of break
     * length, as elapsed time == target time
     *
     * notice - elevated alert, 2 minutes after the break ends, or 10
     * minutes after the lunch ends (those are default values)
     *
     * @param array $emp
     * @return void
     */
    private function _breakNotice($emp)
    {
        extract($emp);
        $break_notice = (int) GlobalSetting::get('break_email_threshold');
        $target = $this->break_length - $break_notice;
        if ($action_duration > $target - $this->buffer && $action_duration <= $target) {
            $this->_playAlert('break', 'notice', $employee_id);
        }
    }

    private function _breakWrapup($emp)
    {
        extract($emp);
        $break_wrapup = (int) GlobalSetting::get('break_wrapup_threshold');
        $target = $this->break_length - $break_wrapup;
        // echo "[ action_duration: $action_duration ]";
        // echo "[ break_wrapup: $break_wrapup ]";
        // echo "[ target: $target ]";
        // echo 'time: ' . time();
        if ($action_duration > $target - $this->buffer && $action_duration <= $target) {
            $this->_playAlert('break', 'warning', $employee_id);
        }
    }

    private function _breakAlert($emp)
    {
        extract($emp);
        $break_alert = (int) GlobalSetting::get('break_alert_threshold');
        $target = $this->break_length - $break_alert;
        // echo "[ action_duration: $action_duration ]";
        // echo "[ break_wrapup: $break_alert ]";
        // echo "[ target: $target ]";
        // echo 'time: ' . time();
        if ($action_duration > $target - $this->buffer && $action_duration <= $target) {
            $this->_playAlert('break', 'over', $employee_id);
        }
    }

    private function _lunchWrapup($emp)
    {
        extract($emp);
        $lunch_wrapup = (int) GlobalSetting::get('lunch_wrapup_threshold');
        $target = $this->lunch_length + $lunch_wrapup;
        if ($action_duration > $target - $this->buffer && $action_duration <= $target) {
            $this->_playAlert('lunch', 'warning', $employee_id);
        }
    }

    private function _lunchAlert($emp)
    {
        extract($emp);
        $lunch_alert = (int) GlobalSetting::get('lunch_alert_threshold');
        $target = $this->lunch_length + $lunch_alert;
        if ($action_duration > $target - $this->buffer && $action_duration <= $target) {
            $this->_playAlert('lunch', 'over', $employee_id);
        }
    }

    private function _lunchNotice($emp)
    {
        extract($emp);
        $lunch_email = (int) GlobalSetting::get('lunch_email_threshold');
        $target = $this->lunch_length + $lunch_email;
        if ($action_duration > $target - $this->buffer && $action_duration <= $target) {
            $this->_playAlert('lunch', 'notice', $employee_id);
        }
    }

    /**
     * Create a new alert for the given employee_id
     *
     * TODO - remove the mail() call after testing period. 10/15/20
     *
     * @param string $type
     * @param string $rule
     * @param int $employee_id
     * @return void
     */
    protected function _playAlert($type, $rule, $employee_id, $emp = [])
    {
        new Alert($type, $rule, $employee_id);
    }
}
