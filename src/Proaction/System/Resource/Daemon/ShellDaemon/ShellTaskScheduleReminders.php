<?php

namespace Proaction\System\Resource\Daemon\ShellDaemon;

use Proaction\Domain\Tasks\Model\Task;
use Proaction\Domain\Tasks\Model\TaskScheduledReminders;
use Proaction\System\Resource\Comms\Comms;

class ShellTaskScheduleReminders extends ShellAlerts
{

    private $mock = false;
    public function __construct()
    {
        // $this->_mock();
        $this->_init();
    }

    private function _mock()
    {
        $this->mock = true;
        Task::p_update(['id' => 100, 'scheduled_start' => date('Y-m-d H:i:s', time() + 10)]);
        TaskScheduledReminders::p_create([
            'task_id' => 100,
            'employee_id' => 14,
            'time_stamp' => date('Y-m-d H:i:s')
        ]);
    }

    private function _init()
    {
        // check for time_stamps with the same H:m date as now()
        $reminders = $this->_getReminders();
        if (empty($reminders)) {
            return;
        }
        $this->_fireReminders($reminders);
    }

    private function _fireReminders($reminderArrary)
    {
        foreach ($reminderArrary as $reminder) {
            $this->_sendReminder($reminder);
        }
    }

    private function _sendReminder($reminder)
    {
        extract($reminder);
        if (!Task::isOpen($task_id) && !$this->mock) {
            return;
        }
        return Comms::send(
            'email',
            'tasks.scheduleReminder',
            ['task_id' => $task_id]
        );
    }

    private function _getReminders()
    {
        return TaskScheduledReminders::whereRaw(
            'DATE_FORMAT(time_stamp, "%Y-%m-%d %H:%i") = ?',
            [date('Y-m-d H:i')]
        )->get();
    }
}
