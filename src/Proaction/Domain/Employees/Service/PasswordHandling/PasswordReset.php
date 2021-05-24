<?php

namespace Proaction\Domain\Employees\Service\PasswordHandling;

use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\Domain\Users\Service\UpdateUserPassword;

/**
 * This class will reset the users current password with a temporary
 * hash (UID class), send the notification email and set the employee
 * up with a forced reset at login
 */
class PasswordReset
{

    private $employee;
    public function __construct($employeeId)
    {
        $this->employee = EmployeeView::find($employeeId);
    }

    public function process()
    {
        $update = new UpdateUserPassword($this->employee);
        return $update->setTemporary();
    }
}
