<?php

namespace Proaction\Domain\Timesheets\Model;

use Proaction\Domain\Clients\Model\ClientIpWhitelist;
use Proaction\System\Model\ClientModel;
use Proaction\System\Resource\Helpers\Arr;

class TimesheetIpLog extends ClientModel
{
    protected $table = 'ts_iplog';
    protected $autoColumns = ['author', '_ip'];

    public $timestamps = false;

    public static function getAlertIpLogs(int $shift_id)
    {
        return (new static)->_getAlertIpLogsByShiftId($shift_id);
    }

    /**
     * Private methods
     */
    private function _getAlertIpLogsByShiftId(int $shift_id)
    {
        $whitelist = ClientIpWhitelist::all('ip_add');
        if (is_null($whitelist)) {
            return null;
        }
        return TimesheetIpLog::where('shift_id', $shift_id)
            ->whereNotIn('_ip', $whitelist->pluck('ip_add')->toArray())
            ->get('_ip', 'timesheet_id as stamp_id');
    }
}
