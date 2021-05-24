<?php

namespace Proaction\Domain\Employees\Model;

use Proaction\System\Database\CDB;
use Proaction\System\Model\ClientModel;
use Proaction\System\Resource\Helpers\Arr;

/**
 * CREATE OR REPLACE VIEW employee_view AS
 * SELECT a.id,
 * a.user_id,
 * a.first_name,
 * a.last_name, a.email, a.nickname, a.phonetic, a.status, a.phone
 * b.department_id,
 * c.department_label,
 * c.bar_color,
 * d.status_id as employment_status_id,
 * e.label as employment_status_label,
 * f.permission_id,
 * CONCAT(a.first_name, " ", UPPER(LEFT(a.last_name, 1)), ".") as displayName,
 * CONCAT(a.first_name, " ", a.last_name) as fullDisplayName
 * FROM employees AS a
 * LEFT JOIN employee_departments b ON b.employee_id=a.id
 * LEFT JOIN departments c ON c.id=b.department_id
 * LEFT JOIN employee_employment_status d ON d.employee_id=a.id
 * LEFT JOIN employee_status_types e ON e.id=d.status_id
 * LEFT JOIN employee_permissions f ON f.employee_id=a.id;
 */

class EmployeeView extends ClientModel
{
    //
    protected $table = 'employee_view';

    protected $pdoName = 'client';
    protected $isView = true;
    public $relations = [];

    public $attributes = [];

    public static function getEmployeesWithActivePayrollRecordsByRange($range)
    {
        $emps = self::leftJoin('v_payroll_records as b', 'b.employee_id', 'employee_view.id')
            ->whereRaw('DATE(b.shift_created_at) BETWEEN ? AND ?', $range)
            ->get([CDB::raw('DISTINCT(b.employee_id) as employee_id'), 'employee_view.*']);
        return $emps->sortBy('id');
    }

    public static function getByUserId($userId)
    {
        return self::where('user_id', $userId)->first(
            [
                'id as employeeId',
                'user_id as userId',
                'first_name as firstName',
                'last_name as lastName',
                'nickname',
                'fullDisplayName',
                'displayName',
                'status',
                'email',
                'department_id as departmentId',
                'department_label as department',
                'bar_color as departmentColor',
                'permission_id as permissionId',
            ]
        );
    }

    public static function getSessionById($userId)
    {
        return (new static)->_getSessionById($userId);
    }

    public static function isActive($id)
    {
        return self::find($id)->status == 1;
    }

    public static function getActiveEmployees($columns = ['*'], $orderBy = null, $sort = null)
    {
        $query = self::where('status', 1);
        if (!is_null($orderBy)) {
            if (!in_array('*', $columns)) {
                if (!in_array($orderBy, $columns)) {
                    throw new \Exception("Error: orderBy value ($orderBy) not present in provided columns");
                }
            }
            $sort = $sort ?? SORT_ASC;
            if ($sort == SORT_ASC) {
                $query->oldest($orderBy);
            } else {
                $query->latest($orderBy);
            }
        }
        if (isset($_GET['test'])) {
            Arr::pre($columns);
        }

        return $query->get($columns);
    }

    public static function getInctiveEmployees($columns = ['*'], $orderBy = null, $sort = null)
    {

        $query = self::where('status', 0);
        if (!is_null($orderBy)) {
            if (!in_array('*', $columns)) {
                if (!in_array($orderBy, $columns)) {
                    throw new \Exception("Error: orderBy value ($orderBy) not present in provided columns");
                }
            }
            $sort = $sort ?? SORT_ASC;
            if ($sort == SORT_ASC) {
                $query->oldest($orderBy);
            } else {
                $query->latest($orderBy);
            }
        }
        if (isset($_GET['test'])) {
            Arr::pre($columns);
        }
        return $query->get($columns);
    }

    private function _getSessionById($userId)
    {
        if (!$_SESSION['user']['sessionLoaded']) {
            return EmployeeView::where('user_id', $userId)->limit(1)->get('id as employeeId', 'status', 'email', 'first_name as firstName', 'last_name as lastName', 'nickname', 'user_id as userId');
        }
        extract($_SESSION['user']);
        return
            compact('employeeId', 'status', 'email', 'firstName', 'lastName', 'nickname', 'userId');
        // return $_SESSION['user'];
    }
}
