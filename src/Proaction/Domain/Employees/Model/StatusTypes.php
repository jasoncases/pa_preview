<?php

namespace Proaction\Domain\Employees\Model;;

use Proaction\System\Model\ClientModel;

class StatusTypes extends ClientModel
{
    protected $table = 'employee_status_types';
    protected $autoColumns = ['edited_by'];
}
