<?php

namespace Proaction\System\Resource\Daemon\ShellDaemon;

class ShellTaskReminders {

    public function __construct()
    {
        $this->_init();
    }

    private function _init() {
        $this->_fireTaskDeadlineReminders();
        $this->_fireTaskScheduledReminders();
    }

    private function _fireTaskDeadlineReminders(){
        return new ShellTaskDeadlineReminders();
    }
    private function _fireTaskScheduledReminders(){
        return new ShellTaskScheduleReminders();
    }
}
