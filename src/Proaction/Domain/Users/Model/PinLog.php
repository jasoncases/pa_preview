<?php

namespace Proaction\Domain\Users\Model;

use Proaction\System\Model\ClientModel;
use Proaction\System\Resource\Config\DotEnv;
use Proaction\System\Resource\Crypto\Crypto;

class PinLog extends ClientModel
{
    //
    protected $table = '_log_user_pin_access';
    protected $autoColumns = ['_ip'];

    protected $pdoName = 'client';

    public $attributes = [];

    public static function saveNewAccessLog(string $pin_attempted, bool $status = null)
    {
        $status = $status ?? 0;
        $pin_attempted = Crypto::encrypt($pin_attempted, DotEnv::get('PIN_LOG_KEY'));
        $unix = time();
        $_ip = $_SERVER['REMOTE_ADDR'];
        return (new static)::p_create(compact('pin_attempted', 'status', 'unix', '_ip'));
    }
}
