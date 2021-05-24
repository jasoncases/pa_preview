<?php

namespace Proaction\System\Database;

use Illuminate\Support\Facades\DB;

class ProactionDB
{
    protected static $connectionName;

    public static function raw($value)
    {
        return DB::connection(static::$connectionName)->raw($value);
    }
}
