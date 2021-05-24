<?php

namespace Proaction\Domain\Timesheets\Resource\EmailActions;

use Proaction\System\Resource\Email\EmailActions\EmailGate;

class TimesheetEmailGate extends EmailGate
{

    protected $messages = [
        'MissingEmployeeRate' => '\Proaction\Domain\Timesheets\Resource\EmailActions\MissingEmployeeRate'
    ];
}
