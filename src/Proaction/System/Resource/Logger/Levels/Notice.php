<?php

namespace Proaction\System\Resource\Logger\Levels;

use Proaction\System\Resource\Logger\LogLevel;

class Notice extends LogLevel
{
    protected $level = 'notice';
    protected function _additionalActions(string $string, $data = [])
    {
    }
}
