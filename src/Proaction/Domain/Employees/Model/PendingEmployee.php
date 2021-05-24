<?php

namespace Proaction\Domain\Employees\Model;;

use Proaction\System\Database\CDB;
use Proaction\System\Model\ClientModel;

class PendingEmployee extends ClientModel
{
    protected $table = 'pending_employee_creation';
    protected $autoColumns = [
        'author',
        'edited_by'
    ];

    private $fields = [
        'id',
        'first_name',
        'last_name',
        'email',
        'nickname',
        'phonetic',
        'phone',
        'date_of_hire',
        'date_of_birth',
        'status_id',
        'rate',
        'department_id',
        'permission_id',
        'pin',
        'allow_remove_timesheet_action'
    ];

    public static function getReview($id)
    {
        return self::where('pending_employee_creation.id', $id)
            ->leftJoin('departments as b', 'b.id', 'department_id')
            ->leftJoin('permission_levels as c', 'c.id', 'permission_id')
            ->leftJoin('employee_status_types as d', 'd.id', 'status_id')
            ->first(
                [
                    'pending_employee_creation.*',
                    'b.department_label',
                    'c.permission_label',
                    CDB::raw('IF(pending_employee_creation.allow_remote_timesheet_action=1, "Yes", "No") as allowRemote'),
                    CDB::raw('FORMAT(pending_employee_creation. rate, 2) as rate'),
                    CDB::raw('DATE_FORMAT(pending_employee_creation. date_of_birth, "%m/%e/%Y") as date_of_birth'),
                    CDB::raw('DATE_FORMAT(pending_employee_creation. date_of_hire, "%m/%e/%Y") as date_of_hire'),
                    CDB::raw('CONCAT(d.label, " (", d.abbr, ")") as employment_label'),
                ]
            );
    }

    public static function phoneIsUnique($phone, $id)
    {
        return !boolval(self::where('phone', $phone)->where('id', '!=', $id)->first());
    }

    public static function emailIsUnique($email, $id)
    {
        return !boolval(self::where('email', $email)->where('id', '!=', $id)->first());
    }

    public static function pinIsUnique($pin, $id)
    {
        return !boolval(self::where('pin', $pin)->where('id', '!=', $id)->first());
    }

    public static function p_update($array)
    {
        return (new static)->_sanitizeAndUpdatePendingRecord($array);
    }

    public static function p_create($array)
    {
        return (new static)->_sanitizeAndCreatePendingRecord($array);
    }

    private function _sanitizeAndCreatePendingRecord($arr)
    {
        foreach ($arr as $k => $v) {
            if (!in_array($k, $this->fields)) {
                unset($arr[$k]);
            }
        }
        return $this->_p_create($arr);
    }

    private function _sanitizeAndUpdatePendingRecord($arr)
    {
        foreach ($arr as $k => $v) {
            if (!in_array($k, $this->fields)) {
                unset($arr[$k]);
            }
        }
        return $this->_p_update($arr);
    }
}
