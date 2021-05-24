<?php

namespace Proaction\System\Resource\Alerts;

use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\System\Model\Alerts\AlertTemplate;
use Proaction\System\Resource\Logger\Log;

/**
 * This class gets the template and employee information for alerts
 * Child classes are VoiceAlert and EmailAlert, each handling the
 * acquired template in their own way
 */
class Alert
{

    protected $type;
    protected $template;
    protected $data = [];
    protected $employeeId;
    protected $name;

    public function __construct(string $name, int $employeeId, $options = [])
    {
        $this->name = $name;
        $this->employeeId = $employeeId;
        $this->_init();
    }

    /**
     * Public function process/send the alert, be it email or voice
     *
     * @return void
     */
    public function send($expiration = 0)
    {
        Log::info('Daemon alert sent.', ['alert_name' => $this->name, 'employee_id' => $this->employeeId]);
        $this->_send($expiration);
    }

    /**
     *
     * @return void
     */
    protected function _init()
    {
        $this->_setData();
        $this->_setTemplate();
    }

    /**
     * Set top-level prop
     * @return void
     */
    protected function _setTemplate()
    {
        $this->template = $this->_getTemplate();
    }

    /**
     * Set top-level prop
     * @return void
     */
    protected function _setData()
    {
        $timestamp = date('Y-m-d H:i:s');
        $this->data = array_merge($this->_getEmployee(), compact('timestamp'));
    }

    /**
     * return employee data
     *
     * @return void
     */
    protected function _getEmployee(): array
    {
        $emp = EmployeeView::where('id', $this->employeeId)->get('id', 'first_name', 'nickname', 'last_name', 'email','fullDisplayName');

        $emp['nickname'] = $emp['nickname'] ?? $emp['first_name'];


        return (array) $emp;
    }

    /**
     * Returns the template from incoming type/name props
     *
     * @return string
     */
    protected function _getTemplate(): string
    {
        return AlertTemplate::___load($this->type, $this->name);
    }

    /**
     * Child methods determine how to handle the _send method
     *
     * @return void
     */
    protected function _send($expiration = 0)
    {
    }
}

/**
 * {"subject":"TIMESTAMP ALERT: {first_name} has exceeded their lunch time","message":"[p]{first_name} has exceeded their lunch time.[/p][p]Timestamp: {timestamp}[/p]"}
 */

/**
 * {"subject":"SYSTEM MESSAGE: {first_name} {last_name} has been clocked out automatically","message":"[h3]{first_name} has been clocked out automatically[/h3][p]The system will automatically clock an employee out after [16 hours]. Please see a manager as soon as possible to correct your time.[/p]"}
 */
