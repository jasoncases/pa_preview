<?php

namespace Proaction\System\Resource\Config;

use Proaction\Domain\Clients\Resource\ProactionClient;

class ProactionConfig {
    private $config;
    private static $__instance;

    private $root = '/home/zerodock/proaction_clients/';
    private $filename = '/config_proaction.json';
    private $path;
    /**
     *
     * @return \Config
     * */
    public static function getInstance()
    {
        if (!self::$__instance) {
            self::$__instance = new static;
        }
        return self::$__instance;
    }

     /**
     * Returns whole config_proaction.json
     *
     * @return array
     * */
    public static function all()
    {
        return self::getInstance()->_all();
    }

    /**
     *
     * @param string $key
     * @return mixed
     * */
    public static function get($key)
    {
        return self::getInstance()->get_value($key);
    }

    /**
     *
     * @param string $key
     * @return mixed
     * */
    public function get_value($key)
    {
        return $this->config[$key];
    }

    /**
     *
     *
     * */
    public function _all()
    {
        return $this->config;
    }

    private function __construct()
    {
        $this->path = $this->_validatePath($this->_buildPath());
        $this->config = $this->_generateConfig();
    }

    private function _buildPath() {
        return $this->root . ProactionClient::prefix() . $this->filename;
    }

    private function _validatePath($path) {
        if (!file_exists($path)) {
            throw new \Exception(
                'Missing config.json ' . print_r(debug_backtrace(), true)
            );
        }
        return $path;
    }

    private function _generateConfig() {
        return json_decode(file_get_contents($this->path), true);
    }
}
