<?php

namespace Proaction\System\Resource\Email\EmailActions;


class AlertLunchNotice extends EmailAction
{
    protected $templateName = 'alert_lunch_notice';
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
        $subject = 'TIMESTAMP ALERT: ' . $this->data['fullDisplayName'] . ' has exceeded their lunch time.';
        $message = $this->_getMessage($this->data);
        return $this->_compose($message, $subject);
    }
}
