<?php

namespace Proaction\Domain\Timesheets\Resource\EmailActions;

use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Employees\Model\EmployeeView;

/**
 * options: $employeeId, $timestamp
 */
class MissingEmployeeRate extends TimesheetEmailAction
{

    protected $templateName = 'missingEmployeeRate';

    private $employeeId;

    public function __construct($options)
    {
        extract($options);
        $this->employeeId = $employeeId;
        $this->_init();
    }

    public function send()
    {
        $emp = EmployeeView::find($this->employeeId);
        $emp->timestamp = date('Y-m-d H:i:s');
        $message = $this->_getMessage($emp);
        $subject = 'Urgent: Missing Payrate At Employee Shift Creation';
        return $this->_compose($message, $subject);
    }
}
