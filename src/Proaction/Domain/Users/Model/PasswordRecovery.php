<?php

namespace Proaction\Domain\Users\Model;

use Proaction\System\Model\ClientModel;

class PasswordRecovery extends ClientModel
{
    //
    protected $table = 'user_password_reset';
    protected $autoColumns = ['deleted_at', 'deleted_by'];
}
