<?php

namespace Proaction\Domain\Employees\Model;

use Proaction\System\Model\ClientModel;

class EmployeePermissions extends ClientModel
{
    protected $table = 'employee_permissions';
    protected $autoColumns = ['edited_by'];

    public static function updatePermission($employee_id, $permission_id)
    {
        $id = self::where('employee_id', $employee_id)->first('id');
        if ($id) {
            return self::p_update(compact('id', 'employee_id', 'permission_id'));
        } else {
            return self::p_create(compact('employee_id', 'permission_id'));
        }
    }
}
