<?php

namespace Proaction\System\Model\AccessLog;

use Proaction\System\Model\ClientModel;

class AccessLog extends ClientModel
{
    protected $table = 'system_access_log';
    protected $autoColumns = ['author', '_ip', 'edited_by'];
    protected $sendReturnId = false;

    /**
     * Undocumented function
     *
     * @param boolean $log
     * @return void
     */
    public static function controllerLog($log = false, $data = [])
    {
        if ($log) {
            return self::new();
        }
        return null;
    }

    public static function new($data = [])
    {
        $_data = json_encode($data);
        $referral_uri = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'none/self/refresh';
        $request_method = $_SERVER['REQUEST_METHOD'];
        $access_uri = $_SERVER['REQUEST_URI'];
        return self::p_create(compact(
            'referral_uri',
            'request_method',
            'access_uri',
            '_data'
        ));
    }
}
