<?php

namespace Proaction\Domain\Users\Model;

use Proaction\System\Model\ClientModel;

class PermissionDetails extends ClientModel
{
    //
    protected $table = 'permission_access_detail';
    protected $autoColumns = ['edited_by'];
}
