<?php

namespace Proaction\Domain\Timesheets\Model\Timeline;

use Proaction\System\Model\ClientModel;

class TimelineView extends ClientModel
{
    protected $table = 'v_timeline';

    protected $isView = true;
    protected $view = 'CREATE OR REPLACE VIEW v_timeline AS
                        SELECT b.id, a.id as shift_id, a.employee_id, a.created_at, DATE(b.time_stamp) as stamp_date, TIME(b.time_stamp) as stamp_time, b.activity_id, b.unix_ts, c.identifier as activity, c.bar_color
                        FROM `ts_shifts` as a
                        LEFT JOIN ts_timesheet b ON b.shift_id=a.id
                        LEFT JOIN worktype_core c ON c.actionId=b.activity_id;';
}
