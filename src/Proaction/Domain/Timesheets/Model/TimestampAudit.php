<?php

namespace Proaction\Domain\Timesheets\Model;

use Proaction\System\Model\ClientModel;

class TimestampAudit extends ClientModel
{
    protected $table = 'ts_audit';
    protected $pdoName = 'client';
    protected $autoColumns = ['author', 'edited_by'];

    public $attributes = [];

    public static function newAudit(Timesheet $t)
    {
        return self::p_create([
            'timesheet_id' => $t->id,
            'unix_ts' => $t->unix_ts,
            'time_stamp' => $t->time_stamp,
            'shift_id' => $t->shift_id,
        ]);
    }
}
