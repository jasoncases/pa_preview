<?php

namespace Proaction\Domain\Timesheets\Model;

use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\System\Database\CDB;
use Proaction\System\Model\ClientModel;

/**
 *
 */

class EmployeeTimeclockStatus extends ClientModel
{
    protected $table = 'v_employee_timeclock_status';
    protected $isView = true;
    protected $view = 'CREATE OR REPLACE VIEW v_employee_timeclock_status AS
                        SELECT b.id, b.activity_id, b.time_stamp, b.unix_ts, b.employee_id FROM
                        ts_timesheet AS b
                        WHERE id IN
                        # get the last stamp from the shift id, within
                        (SELECT MAX(id) FROM ts_timesheet WHERE shift_id IN
                        # all active shifts
                        (SELECT id FROM ts_shifts WHERE active=1)
                        GROUP BY employee_id);';

    protected static $defaultStatusText = 'Clocked In';
    protected static $defaultStatusColor = 'hsl(135, 100%, 40%)';
    protected static $defaultStatusReadable = 'You are clocked in';

    public static function getActiveStatus($id = null)
    {
        return (new static)->_getActiveStatus($id);
    }

    private function _getActiveStatus($id = null)
    {
        return $this->_formatEmployeeStatusStructure(
            $this->_getAllEmployeeActiveStatus($id)
        );
    }

    private function _formatEmployeeStatusStructure($emp)
    {
        $c = [];
        foreach ($emp as $e) {
            $c[$e['employeeIdHash']] = $this->_formatEmployee($e);
        }
        return $c;
    }

    private function _formatEmployee($emp)
    {
        $c = [];
        $c['employeeId'] = $emp['employeeId'];
        $c['email'] = $emp['email'];
        $c['firstName'] = $emp['first_name'];
        $c['lastName'] = $emp['last_name'];
        $c['nickname'] = $emp['nickname'];
        $c['displayName'] = $emp['displayName'];
        $c['fullDisplayName'] = $emp['fullDisplayName'];
        $c['hash'] = $emp['employeeIdHash'];
        $c['lastActivity'] = [
            'datetime' => $emp['lastActivityDatetime'],
            'timestamp' => $emp['lastActivityTimestamp'],
            'activityId' => $emp['lastActivityActionId'],
        ];
        $c['departmentInfo'] = [
            'id' => $emp['department_id'],
            'label' => $emp['department_label'],
            'color' => $emp['department_color'],
        ];
        $c['currentStatus'] = [
            'text' => $this->_statusText($emp),
            'readable' => $this->_statusReadable($emp),
            'color' => $this->_statusColor($emp),
            'activityId' => $emp['lastActivityActionId'] ?? 0,
            'shiftId' => $emp['shift_id']
        ];
        $c['hydrated'] = $emp['hydrated'];
        return $c;
    }

    private function _statusColor($emp)
    {
        if ($emp['lastActivityActionId'] < 0) {
            return self::$defaultStatusColor;
        }
        return $emp['currentStatusColor'];
    }

    private function _statusReadable($emp)
    {
        if ($emp['lastActivityActionId'] < 0) {
            return self::$defaultStatusReadable;
        }
        return $emp['currentStatusReadable'];
    }

    private function _statusText($emp)
    {
        if ($emp['lastActivityActionId'] < 0) {
            return self::$defaultStatusText;
        }
        return $emp['currentStatusText'];
    }

    private function _getAllEmployeeActiveStatus($id = null)
    {
        if (!is_null($id)) {
            $q = EmployeeView::where('employee_view.id', $id);
        } else {
            $q = EmployeeView::where('employee_view.status', 1);
        }
        return $q->leftJoin('v_employee_timeclock_status as b', 'b.employee_id', 'employee_view.id')
            ->leftJoin('worktype_core as c', 'c.actionId', 'b.activity_id')
            ->leftJoin('ts_timesheet as d', 'd.id', 'b.id')
            ->get(
                [
                    'b.time_stamp as lastActivityDatetime',
                    'b.unix_ts as lastActivityTimestamp',
                    'b.activity_id as lastActivityActionId',
                    'd.shift_id',
                    'employee_view.id as employeeId',
                    'employee_view.displayName',
                    'employee_view.fullDisplayName',
                    'employee_view.nickname',
                    'employee_view.first_name',
                    'employee_view.last_name',
                    'employee_view.email',
                    // department info
                    'employee_view.department_label',
                    'employee_view.department_id',
                    'employee_view.bar_color as department_color',
                    // current status info
                    CDB::raw('IF(c.text IS NULL, "Clock Out", c.text) as currentStatusText'),
                    CDB::raw('IF(c.status IS NULL, "You are clocked out", c.status) as currentStatusReadable'),
                    CDB::raw('IF(c.bar_color IS NULL, "hsl(7, 84%, 53%)", c.bar_color) as currentStatusColor'),
                    CDB::raw('UNIX_TIMESTAMP(NOW()) as hydrated'),
                    CDB::raw('MD5(employee_view.id) as employeeIdHash'),
                ]
            )->toArray();
    }
}
