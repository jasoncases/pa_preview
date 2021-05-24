<?php

namespace Proaction\System\Resource\Email\EmailActions;


class ForcedClockout extends EmailAction
{
    protected $templateName = 'forced_clockout';
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
        $subject = 'SYSTEM MESSAGE: ' . $this->data['fullDisplayName'] . ' has been clocked out automatically.';
        $message = $this->_getMessage($this->data);
        return $this->_compose($message, $subject);
    }
}
