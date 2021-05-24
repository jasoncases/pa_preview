<?php

namespace Proaction\System\Resource\Data;

use Proaction\System\Resource\Helpers\Arr;

/**
 * A Repository for all data to be sent to the view.
 *
 * ! This is one of those things that can be removed once the full app
 * ! is ported to a proper laravel application
 *
 */
class Data {
    private static $__instance;

    private $data = [];

    public static function getInstance() {
        if (!self::$__instance) {
            self::$__instance = new Data();
        }
        return self::$__instance;
    }

    public static function add($key, $value = null) {
        self::getInstance()->_add($key, $value);
    }

    public function _add($key, $value=null) {
        if (isset($_GET['tt'])) {
            echo "<hr />";
            Arr::pre($key);
            Arr::pre($value);
            echo "<hr />";
        }
        if (is_array($key) && is_null($value)) {
            foreach ($key as $k => $v) {
                $this->data[$k] = $v;
            }
        } else {
            $this->data[$key] = $value;
        }
    }

    public function get() {
        return $this->data;
    }

    public function override($array) {
        $this->data = $array;
    }
}
