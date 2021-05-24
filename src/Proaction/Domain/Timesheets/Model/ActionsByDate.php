<?php

namespace Proaction\Domain\Timesheets\Model;

use Proaction\System\Model\ClientModel;

/**
 * CREATE OR REPLACE VIEW v_actions_by_date AS
 * SELECT a.created_at, b.activity_id, b.employee_id, b.id, DATE(b.time_stamp) as date, TIME(b.time_stamp) as time, c.text FROM ts_shifts as a
 * LEFT JOIN ts_timesheet b ON b.shift_id=a.id
 * LEFT JOIN worktype_core c ON c.actionId=b.activity_id
 * WHERE b.activity_id BETWEEN -10 AND 10;
 *
 * */
class ActionsByDate extends ClientModel
{
    protected $table = 'v_actions_by_date';
    protected $isView = true;
    protected $view = 'CREATE OR REPLACE VIEW v_actions_by_date AS
                        SELECT a.created_at, b.activity_id, b.employee_id, b.id, DATE(b.time_stamp) as date, TIME(b.time_stamp) as time, c.text FROM ts_shifts as a
                        LEFT JOIN ts_timesheet b ON b.shift_id=a.id
                        LEFT JOIN worktype_core c ON c.actionId=b.activity_id
                        WHERE b.activity_id BETWEEN -10 AND 10;';
}
