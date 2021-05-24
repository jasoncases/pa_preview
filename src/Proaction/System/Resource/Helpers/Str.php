<?php

namespace Proaction\System\Resource\Helpers;

class Str
{

    public static function camel_case(string $string)
    {
        return lcfirst(self::pascal_case($string));
    }

    public static function pascal_case(string $string)
    {
        $string = ucwords(str_replace(['-', '_'], ' ', $string));
        return str_replace(' ', '', $string);
    }

    public static function snake_case(string $string, $delimiter = '_')
    {
        $value = $string;

        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = self::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        }

        return $value;
    }

    public static function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }

    public static function lower(string $string)
    {
        return strtolower($string);
    }

    public static function isJSON($string)
    {
        if (gettype($string) != 'string') {
            return false;
        }
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    public static function trunc($string, $len)
    {
        return strlen($string) > $len ? substr($string, 0, $len - 3) . '...' : $string;
    }

    public static function sanitize($str)
    {
        return (new static)->_sanitizeString($str);
    }

    private function _sanitizeString($str)
    {
        $str = str_replace('&', 'and', $this->_stripQuotes($str));
        return $str;
    }

    public static function stripQuotes($str){
        return (new static)->_stripQuotes($str);
    }

    private function _stripQuotes($str){
        $str = str_replace('"', '', $str);
        $str = str_replace("'", "", $str);
        return $str;
    }
}
