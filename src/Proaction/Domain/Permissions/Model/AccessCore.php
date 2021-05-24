<?php

namespace Proaction\Domain\Permissions\Model;

use Proaction\System\Model\ClientModel;

class AccessCore extends ClientModel
{
    protected $table = 'permission_access_core';
    protected $autoColumns = ['edited_by'];
}
