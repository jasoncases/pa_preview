<?php

namespace Proaction\System\Resource\Logger\Levels;

use Proaction\System\Resource\Logger\LogLevel;

class Debug extends LogLevel
{
    protected $level = 'debug';
    protected function _additionalActions(string $string, $data = [])
    {
    }
}
