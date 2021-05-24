<?php

namespace Proaction\System\Resource\Email\EmailActions;


class Tardy extends EmailAction
{

    protected $templateName = 'tardy_alert';
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
        $subject = 'Proaction - Employee Running Late';
        $message = $this->_getMessage($this->data);
        return $this->_compose($message, $subject);
    }
}
