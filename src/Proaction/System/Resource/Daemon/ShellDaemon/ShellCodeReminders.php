<?php

namespace Proaction\System\Resource\Daemon\ShellDaemon;

use Proaction\Domain\Codes\Model\Code;
use Proaction\Domain\Codes\Model\CodeScheduledReminders;
use Proaction\System\Resource\Comms\Comms;

class ShellCodesReminders extends ShellAlerts
{

    public function __construct()
    {
        $this->_init();
    }

    private function _init()
    {
        $reminders = $this->_getReminders();
        foreach ($reminders as $reminder) {
            if (Code::isOpen($reminder['code_id'])) {
                Comms::send('email', 'code.scheduleReminder', ['code_id' => $reminder['code_id']]);
            }
        }
    }

    private function _getReminders()
    {
        return CodeScheduledReminders::whereRaw(
            'DATE_FORMAT(time_stamp, "%Y-%m-%d %H:%i") = ?',
            [date('Y-m-d H:i')]
        )
            ->get();
    }
}
