<?php

namespace Proaction\System\Resource\Logger\Levels;

use Proaction\System\Resource\Logger\LogLevel;

class Critical extends LogLevel
{
    protected $level = 'critical';
    protected function _additionalActions(string $string, $data = [])
    {
    }
}
