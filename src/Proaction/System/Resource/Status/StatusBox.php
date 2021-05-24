<?php

namespace Proaction\System\Resource\Status;

class StatusBox
{

    private static $__instance;

    public $status = false;
    public $message = '';

    public static function create($msg)
    {
        return self::getInstance()->_new($msg);
    }

    private function _new($msg)
    {
        $this->status = true;
        $this->message = $msg;
    }

    public static function getInstance()
    {
        if (!self::$__instance) {
            self::$__instance = new StatusBox();
        }
        return self::$__instance;
    }
}
