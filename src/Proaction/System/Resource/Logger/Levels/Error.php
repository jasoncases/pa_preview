<?php

namespace Proaction\System\Resource\Logger\Levels;

use Proaction\System\Resource\Logger\LogLevel;

class Error extends LogLevel
{
    protected $level = 'error';
    protected function _additionalActions(string $string, $data = [])
    {
    }
}
