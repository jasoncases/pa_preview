<?php

namespace Proaction\System\Model\Alerts;

use Proaction\System\Model\ClientModel;

class AutomatedAlert extends ClientModel
{
    protected $table = 'system_automated_alerts';


    public static function archive(string $message, $employee_id, $expiration = 0)
    {
        return (new static)->_archive($message, $employee_id, $expiration);
    }

    public static function scan(string $message, $employee_id, $expiration = 0)
    {
        return (new static)->_scan($message, $employee_id, $expiration);
    }

    private function _archive(string $text, int $employee_id, int $expiration)
    {
        $hash = md5($text);
        return self::p_create(compact('text', 'hash', 'employee_id', 'expiration'));
    }

    private function _scan(string $text, $employee_id, $expiration = 0)
    {
        $hash = md5($text);
        $expiration = boolval($expiration) ? time() + $expiration : 0;
        if ($this->_freshMessageExists($hash)) {
            return false;
        }
        return self::archive($text, $employee_id, $expiration);
    }

    private function _freshMessageExists(string $hash)
    {
        return self::where('hash', $hash)
            ->where('expiration', '>', time())
            ->last()
            ->get();
    }
}
