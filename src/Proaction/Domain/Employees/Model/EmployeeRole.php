<?php

namespace Proaction\Domain\Employees\Model;

use Proaction\System\Model\ClientModel;

class EmployeeRole extends ClientModel
{
    protected $table = 'employee_roles';
    protected $autoColumns = ['edited_by'];
}
