<?php

namespace Proaction\Domain\Payroll\Model;

use Proaction\System\Model\ClientModel;

class PTOAllowed extends ClientModel
{
    protected $table = 'payroll_employee_pto_allowed';
    protected $autoColumns = ['edited_by'];
}
