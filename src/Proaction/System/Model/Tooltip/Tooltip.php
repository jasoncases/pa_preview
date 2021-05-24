<?php

namespace Proaction\System\Model\Tooltip;

use Proaction\System\Model\ClientModel;

class Tooltip extends ClientModel
{
    protected $table = 'tooltips';
    protected $pdoName = 'meta';

    public static function getByName($name)
    {
        return self::where('name', $name)->get('text');
    }
}
