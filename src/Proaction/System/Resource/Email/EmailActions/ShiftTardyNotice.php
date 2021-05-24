<?php

namespace Proaction\System\Resource\Email\EmailActions;


class ShiftTardyNotice extends EmailAction
{
    protected $templateName = 'shift_tardy_notice';
    protected $addAdmin = true;
    protected $moduleName = 'Attendance Tracker';
    private $data;
    public function __construct($options)
    {
        extract($options);
        $this->data = $data;
        $this->_init();
    }

    public function send()
    {
        $subject = 'TIMESTAMP ALERT: ' . $this->data['fullDisplayName'] . ' is 15 minutes late to work.';
        $message = $this->_getMessage($this->data);
        return $this->_compose($message, $subject);
    }
}
