<?php

namespace Proaction\System\Resource\Logger\Levels;

use Proaction\System\Resource\Logger\LogLevel;

class Warning extends LogLevel
{
    protected $level = 'warning';
    protected function _additionalActions(string $string, $data = [])
    {
    }
}
