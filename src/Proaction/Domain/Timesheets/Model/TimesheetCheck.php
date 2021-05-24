<?php

namespace Proaction\Domain\Timesheets\Model;

use Proaction\System\Model\ClientModel;

class TimesheetCheck extends ClientModel
{
    protected $table = 'ts_shift_approved_by_manager';
    protected $autoColumns = ['manager_id'];


    public $attributes = [];

}
