<?php

namespace Proaction\Domain\Permissions\Model;

use Proaction\System\Model\ClientModel;

class AccessDetail extends ClientModel
{
    protected $table = 'permission_access_detail';
    protected $autoColumns = ['edited_by'];

    public static function getGetByPermissionId($id)
    {
        return self::where('permission_id', $id)
            ->leftJoin('permission_access_core', 'permission_access_core.id', '=', 'permission_access_detail.access_id')
            ->get(
                [
                    'permission_access_core.permission_short_name as name',
                    'permission_access_detail.value',
                ]
            );
    }

    public static function saveNewPermissionGroup($permissionGroupArr)
    {
        return (new static)->_saveNewGroup($permissionGroupArr);
    }

    public static function updatePermissionGroup($permissionGroupArr)
    {
        return (new static)->_updateGroup($permissionGroupArr);
    }


    private function _updateGroup($grpArr)
    {
        extract($grpArr);
        $this->_updatePermissionLevel($id, $permission_label);
        $this->_clearAccessDetailByPermissionId($id);
        $this->_saveGroupByPermissionId($grpArr, $id);
    }

    private function _saveNewGroup($grpArr)
    {
        $permission_id =  $this->_saveNewPermissionLevel($grpArr["permission_label"]);
        $this->_saveGroupByPermissionId($grpArr, $permission_id);
    }

    private function _saveGroupByPermissionId($grpArr, $permission_id)
    {
        $access_ids = $this->_allAccessCore();
        $c = [];
        foreach ($access_ids as $acc) {
            extract($acc);
            $keys = array_keys($grpArr);
            $c[] = [
                'permission_id' => $permission_id,
                'access_id' => $id,
                'value' => in_array($permission_short_name, $keys) ?? 0,
            ];
        }

        $this->_saveNewAccessDetailGroup($c);
    }

    private function _clearAccessDetailByPermissionId($permission_id)
    {
        AccessDetail::deleteWhere("permission_id", $permission_id);
    }

    private function _saveNewAccessDetailGroup($detailArr)
    {
        foreach ($detailArr as $arr) {
            AccessDetail::p_create($arr);
        }
    }

    private function _allAccessCore()
    {
        return AccessCore::where('status', 1)->get(['permission_short_name', 'id']);
    }

    private function _saveNewPermissionLevel($permission_label)
    {
        return PermissionLevels::p_create(compact('permission_label'));
    }

    private function _updatePermissionLevel($id, $permission_label)
    {
        return PermissionLevels::p_update(compact('id', 'permission_label'));
    }
}
