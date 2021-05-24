<?php

namespace Proaction\Domain\Employees\Model;

use Proaction\System\Model\ClientModel;

class Role extends ClientModel
{
    //
    protected $table = 'client_role_definitions';
    protected $autoColumns = ['edited_by'];

    public $attributes = [];

}
