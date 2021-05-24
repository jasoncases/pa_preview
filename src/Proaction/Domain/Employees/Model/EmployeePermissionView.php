<?php

namespace Proaction\Domain\Employees\Model;

use Illuminate\Support\Arr as SupportArr;
use Proaction\Domain\Permissions\Model\AccessDetail;
use Proaction\Domain\Permissions\Model\PermissionLevels;
use Proaction\System\Model\ClientModel;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Session\UserSession;

/**
 * CREATE OR REPLACE VIEW v_employee_permissions AS
 * SELECT a.*, b.access_id, b.value, c.permission_short_name FROM employee_permissions AS a
 * LEFT JOIN permission_access_detail b ON b.permission_id=a.permission_id
 * LEFT JOIN permission_access_core c on c.id = b.access_id;
 */
class EmployeePermissionView extends ClientModel
{
    protected $table = 'v_employee_permissions';
    protected $isView = true;
    protected $view = 'CREATE OR REPLACE VIEW v_employee_permissions AS
                        SELECT a.*, b.access_id, b.value, c.permission_short_name
                        FROM employee_permissions AS a
                        LEFT JOIN permission_access_detail b ON b.permission_id=a.permission_id
                        LEFT JOIN permission_access_core c on c.id = b.access_id;';
    protected $pdoName = 'client';

    public static function getByPermissionLevelId($id)
    {
        if ($id == PermissionLevels::getGuestId()) {
            return (new static)->_getGuestPermissions($id);
        }
        return (new static)->_getPermissionsByFieldAndId('permission_id', $id);
    }

    private function _getGuestPermissions($id)
    {
        $plid = 'permission_level_id';
        $userSession = new UserSession();
        $currentPermission = $userSession->pluck('permissions');

        if (is_null($currentPermission) || $currentPermission[$plid] != $id) {
            $unformatted = AccessDetail::getGetByPermissionId($id);
            $permissions = $this->_formatEmployeePermissions($unformatted);
            $permissions[$plid] = $id;
            $userSession->add('permissions', $permissions);
        }
        return $userSession->pluck('permissions');
    }

    public static function getByEmployeeId($id)
    {
        return (new static)->_getPermissionsByFieldAndId('employee_id', $id);
    }

    private function _getPermissionsByFieldAndId($field, $id)
    {
        $plid  =  'permission_level_id';
        $userSession = new UserSession();

        $currentPermission = $userSession->pluck('permissions');
        if (is_null($currentPermission) || $currentPermission[$plid] != $id) {
            $foundPermissions = $this->_loadAndFormatPermissions($field, $id);
            $foundPermissions[$plid] = $id;
            $userSession->add('permissions', $foundPermissions);
        }
        return $userSession->pluck('permissions');
    }

    private function _loadAndFormatPermissions($field, $id)
    {
        return $this->_formatEmployeePermissions(
            self::where($field, $id)->get(
                ['value', 'permission_short_name as name']
            )
        );
    }

    private function _formatEmployeePermissions($permissions)
    {
        $c = [];
        foreach ($permissions as $permission) {
            $c[$permission->name] = $permission->value;
        }
        return $c;
    }
}
