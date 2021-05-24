<?php

namespace Proaction\Domain\Payroll\Model;

use Proaction\System\Model\ClientModel;

class PTORequest extends ClientModel
{
    protected $table = 'payroll_employee_pto_requests';
    protected $autoColumns = ['edited_by', 'manager_id'];

}
