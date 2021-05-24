<?php

namespace Proaction\Domain\Users\Model;

use Proaction\System\Model\ClientModel;

class ForcePasswordReset extends ClientModel
{
    protected $table = 'user_force_password_reset';

    public static function exists($user_id)
    {
        return self::whereNull('deleted_at')
            ->where('user_id', $user_id)
            ->first();
    }
}
