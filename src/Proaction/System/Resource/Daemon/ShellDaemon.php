<?php

namespace Proaction\System\Resource\Daemon\ShellDaemon;

use Proaction\Model\ShellTest;
use Proaction\Resource\Daemon\Shell\Codes\ShellCodesReminders;
use Proaction\Resource\Daemon\Shell\Tasks\ShellTaskReminders;

class ShellDaemon
{
    private $Schedule;
    private $Timeclock;
    public function __construct()
    {
        $this->_init();
    }

    private function _init()
    {
        $this->_setTimeZone();
        $this->_checkSchedule();
        $this->_checkEmployeeTimeclockState();
        $this->_checkCodeScheduledReminders();
        $this->_checkScheduleAnnouncements();
        $this->_taskManagerReminders();
    }

    private function _setTimeZone()
    {
        date_default_timezone_set('America/New_York');
    }


    private function _checkCodeScheduledReminders()
    {
        new ShellCodesReminders();
    }

    private function _checkScheduleAnnouncements()
    {
        new ShellVoice();
    }

    private function _checkSchedule()
    {
        new ShellSchedule();
    }
    private function _checkEmployeeTimeclockState()
    {
        //
    }

    private function _taskManagerReminders()
    {
        return new ShellTaskReminders();
    }
}
