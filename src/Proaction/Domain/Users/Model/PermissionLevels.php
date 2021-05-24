<?php

namespace Proaction\Domain\Users\Model;

use Proaction\System\Model\ClientModel;

class PermissionLevels extends ClientModel
{
    //
    protected $table = 'permission_levels';
    protected $autoColumns = ['author', 'edited_by'];

    public $attributes = [];
}
