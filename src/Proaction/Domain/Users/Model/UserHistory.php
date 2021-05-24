<?php

namespace Proaction\Domain\Users\Model;

use Proaction\System\Model\ClientModel;

class UserHistory extends ClientModel
{
    protected $table = 'user_history';
    protected $pdoName = 'client';
}
