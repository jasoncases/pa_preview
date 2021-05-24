<?php

namespace Proaction\System\Resource\Daemon\Alerts;

/**
 * Daemon alert class. These alerts are created after specific actions
 * happen on the bash Daemon process. Typically these are situations
 * where an employee is late for shift, or break/lunch.
 * Creating a process where this runs on the server, calculating
 * duration and updating the state on an incoming action, rather than
 * continually pinging the DB for state changes should lead to a cleaner
 * Front-end experience while adding a minimal amount of processing
 * strain on the backend.
 *
 * Also limits terminals pinging the DB repeatedly, which was a goal
 */
class Alert
{

    /**
     * Creates an alert based on the incoming values
     *
     * @param string $type
     * @param string $rule
     * @param integer $employee_id
     */
    public function __construct(string $type, string $rule, int $employee_id)
    {
        return $this->_createAlert($type, $rule, $employee_id);
    }

    /**
     * Use the type value to determine the class, naming convention:
     * {Type}Alert, then pass the rule, which will match the name of a
     * method in {Type}Alert, and employee_id, so the Alert knows which
     * employee name to put in the template response
     *
     * @param string $type
     * @param string $rule
     * @param integer $employee_id
     * @return void
     */
    private function _createAlert(string $type, string $rule, int $employee_id)
    {
        $class = '\Proaction\Resource\Daemon\Alert\\' . ucfirst($type) . 'Alert';
        return new $class($rule, $employee_id);
    }
}
