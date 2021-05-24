<?php

namespace Proaction\System\Resource\Logger\Levels;

use Proaction\System\Resource\Logger\LogLevel;

class Emergency extends LogLevel
{
    protected $level = 'emergency';
    protected function _additionalActions(string $string, $data = [])
    {
    }
}
