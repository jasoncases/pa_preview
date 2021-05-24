<?php

namespace Proaction\System\Resource\Logger\Levels;

use Proaction\System\Resource\Logger\LogLevel;

class Alert extends LogLevel
{
    protected $level = 'alert';
    protected function _additionalActions(string $string, $data = [])
    {
    }
}
