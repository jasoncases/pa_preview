<?php

namespace Proaction\System\Resource\Logger\Levels;

use Proaction\System\Resource\Logger\LogLevel;

class Info extends LogLevel
{
    protected $level = 'info';
    protected function _additionalActions(string $string, $data = [])
    {
    }
}
