<?php

namespace Proaction\System\Resource\Email\EmailActions;

class EmpCallout extends EmailAction
{

    protected $templateName = 'absence_alert';
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
        $subject = 'Proaction - Employee Call Out';
        $message = $this->_getMessage($this->data);
        return $this->_compose($message, $subject);
    }
}
