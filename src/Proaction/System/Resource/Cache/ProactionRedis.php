<?php

namespace Proaction\System\Resource\Cache;


class ProactionRedis {

    public $redis;

    protected $defaultDb = 1;

    private static $__instance;

    private function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
        $this->redis->select($this->defaultDb);
    }

    public static function getInstance() {
        if (!self::$__instance) {
            self::$__instance = new ProactionRedis();
        }
        return self::$__instance;
    }

}
