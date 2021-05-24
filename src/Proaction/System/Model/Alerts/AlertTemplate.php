<?php

namespace Proaction\System\Model\Alerts;

use Proaction\System\Model\ClientModel;

class AlertTemplate extends ClientModel
{
    protected $table = 'alerts_template';

    public static function ___load(string $type, string $name)
    {
        return self::where('type', $type)
            ->where('name', $name)
            ->get('text');
    }
}
