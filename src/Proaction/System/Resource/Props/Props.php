<?php

namespace Proaction\System\Resource\Props;

use function Proaction\System\Lib\isJson;

class Props
{

    public function __construct($props)
    {
        $this->_init($props);
    }

    /**
     * Set each incoming value to a top-level property on the Props
     * object, accessed in controllers as $this->props
     *
     * @param [type] $props
     * @return void
     */
    private function _init($props)
    {
        // removing values from the data array that may be present, but
        // are not needed once the route is parsed
        $blacklist = ['uri', '__METHOD'];
        foreach ($props as $key => $val) {
            if (!in_array($key, $blacklist)) {
                if ($this->_isJson($val)) {
                    $val = json_decode($val, true);
                }
                $this->{$key} = $this->_sanitize($val);
            }
        }
    }

    /**
     * Run all values to sanitation process
     *
     * @param [type] $value
     * @return mixed
     */
    private function _sanitize($value)
    {
        if (is_array($value) || is_object($value)) {
            return $this->_arraySanitize((array) $value);
        }
        return $value;
    }

    /**
     * Cycle through an array and sanitize each value
     *
     * @param array $array
     * @return void
     */
    private function _arraySanitize(array $array)
    {
        $c = [];
        foreach ($array as $k => $v) {
            $c[$k] = $this->_sanitize($v);
        }
        return $c;
    }

    public function add(string $key, $value)
    {
        $this->{$key} = $value;
    }

    private function _isJson($str)
    {
        try {
            json_decode($str);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
