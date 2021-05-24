<?php

namespace Proaction\Domain\Timesheets\Model;

use Proaction\System\Model\ClientModel;

// CREATE OR REPLACE VIEW v_timestamp_data AS
// SELECT
// a.created_at,
// a.activity_id,
// a.employee_id,
// a.shift_id,
// HOUR(a.time_stamp) as hh,
// MINUTE(a.time_stamp) as mm,
// SECOND(a.time_stamp) as ss,
// YEAR(a.time_stamp) as year,
// MONTH(a.time_stamp) as month,
// DAY(a.time_stamp) as day,
// DATE(a.time_stamp) as date,
// TIME(a.time_stamp) as time,
// b.text
// FROM ts_timesheet AS a
// LEFT JOIN worktype_core b ON b.actionId=a.activity_id;


class TimestampView extends ClientModel
{
    protected $table = 'v_timestamp_data';
    protected $isView = true;
    protected $view = 'CREATE OR REPLACE VIEW v_timestamp_data AS
                    SELECT
                    a.created_at,
                    a.activity_id,
                    a.employee_id,
                    a.shift_id,
                    HOUR(a.time_stamp) as hh,
                    MINUTE(a.time_stamp) as mm,
                    SECOND(a.time_stamp) as ss,
                    YEAR(a.time_stamp) as year,
                    MONTH(a.time_stamp) as month,
                    DAY(a.time_stamp) as day,
                    DATE(a.time_stamp) as date,
                    TIME(a.time_stamp) as time,
                    b.text
                    FROM ts_timesheet AS a
                    LEFT JOIN worktype_core b ON b.actionId=a.activity_id;';
}
