<?php

namespace Proaction\Domain\Permissions\Model;

use Proaction\System\Model\ClientModel;

class PermissionLevels extends ClientModel
{
    protected $table = 'permission_levels';
    protected $autoColumns = ['author', 'edited_by'];

    private static $guest = 'guest';
    private static $guestId = null;

    /**
     * Return an array of permission levels that Client has set to admin
     * level access
     *
     * @return array [int]
     */
    public static function getAdministratorLevelIds()
    {
        $admins = self::where('is_admin', 1)->get();
        return $admins->pluck('id')->toArray();
    }

    public static function isSuperAdmin($permission_id)
    {
        $permissions = self::where('id', $permission_id)
            ->get('is_super')
            ->first();

        return boolval($permissions) ? $permissions->is_super : null;
    }

    public static function getGuestId()
    {
        if (!self::$guestId) {
            self::$guestId = self::getGuestPermissionId();
        }
        return self::$guestId;
    }

    private $restrictedLabels = ['guest', 'none', 'null', 'nil',];

    public static function getGuestPermissionId()
    {
        $guest = '%' . self::$guest . '%';
        return self::where('permission_label', 'LIKE', $guest)
            ->oldest('id')
            ->limit(1)
            ->get('id')
            ->first()
            ->id;
    }
}
