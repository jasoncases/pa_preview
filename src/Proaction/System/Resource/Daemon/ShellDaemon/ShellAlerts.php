<?php

namespace Proaction\System\Resource\Daemon\ShellDaemon;

use Proaction\System\Resource\Daemon\Alerts\Alert;
use Proaction\System\Resource\Logger\Log;

class ShellAlerts
{
    protected $buffer = 60;
    protected $employees = [];

    public function __construct($employeeArray)
    {
        $this->employees = $employeeArray;
        $this->_processAlerts();
    }

    protected function _processAlerts()
    {
    }

    /**
     * Create a new alert for the given employee_id
     *
     * @param string $type
     * @param string $rule
     * @param int $employee_id
     * @return void
     */
    protected function _playAlert($type, $rule, $employee_id)
    {
        try {
            Log::info("Alert created - $type $rule - empId: " . $employee_id);
            return new Alert($type, $rule, $employee_id);
        }catch (\Exception $e){
            Log::error("Error creating alert: " . $e->getMessage());
        }
    }
}
