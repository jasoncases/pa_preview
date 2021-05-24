<?php

namespace Proaction\System\Model;

use Proaction\System\Model\ProactionModel;

class MetaGlobal extends ProactionModel
{
    protected $table = 'meta_globals';
    protected $pdoName = 'meta';

    public static function getBatch($key)
    {
        return self::where('constant', $key)->get('value')->pluck('value');
    }

    public static function get($key)
    {
        $m = self::where('constant', $key)->first('value');
        return is_null($m) ? [] : $m->value;
    }
}
