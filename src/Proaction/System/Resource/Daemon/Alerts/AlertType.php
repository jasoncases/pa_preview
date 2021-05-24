<?php

namespace Proaction\System\Resource\Daemon;

use Proaction\System\Helpers\Arr;

/**
 * Parent class to all Daemon Alerts, {Type}Alert, classes.
 */
class AlertType
{

    protected $employeeId;
    protected $rule;
    protected $type;

    public function __construct(string $rule, int $employee_id)
    {
        $this->rule = $rule;
        $this->employeeId = $employee_id;
        return $this->_process();
    }

    /**
     * process the incoming rule value and determine the method to call
     *
     * @return void
     */
    protected function _process()
    {
        // just for consistency's sake, use strtolower and call method
        $method = strtolower($this->rule);
        return $this->{$method}();
    }

    /**
     *
     * @param string $alertName
     * @return \Proaction\Resource\Alert\VoiceAlert
     */
    protected function _getVoiceAlert(string $alertName): \Proaction\Resource\Alert\VoiceAlert
    {
        return new \Proaction\Resource\Alert\VoiceAlert($alertName, $this->employeeId);
    }

    /**
     *
     * @param string $alertName
     * @return \Proaction\Resource\Alert\EmailAlert
     */
    protected function _getEmailAlert(string $alertName): \Proaction\Resource\Alert\EmailAlert
    {
        return new \Proaction\Resource\Alert\EmailAlert($alertName, $this->employeeId);
    }
}
