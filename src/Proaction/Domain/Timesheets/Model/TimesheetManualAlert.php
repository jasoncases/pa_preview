<?php

namespace Proaction\Domain\Timesheets\Model;

use Proaction\System\Model\ClientModel;

class TimesheetManualAlert extends ClientModel
{
    protected $table = 'ts_manual_alert';
    protected $autoColumns = ['author', 'edited_by'];

    public $attributes = [];

}
