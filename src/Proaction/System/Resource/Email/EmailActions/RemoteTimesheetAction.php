<?php

namespace Proaction\System\Resource\Email\EmailActions;


class RemoteTimesheetAction extends EmailAction
{

    protected $templateName = 'remote_timesheet_action';
    protected $addAdmin = true;
    protected $moduleName = 'Timesheet Admin';

    public function __construct($options)
    {
        extract($options);
        $this->data = $this->_getData($options);
        $this->_init();
    }

    public function send()
    {
        $subject = 'Remote Timeclock Action Detected';
        $message = $this->_getMessage($this->data);
        return $this->_compose($message, $subject);
    }

    protected function _getData($options = [])
    {
        return $options;
    }
}
