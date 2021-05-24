<?php

namespace Proaction\System\Resource\Helpers;

class Domain
{

    private static $_devWhiteList = ['dev'];

    public static function get()
    {
        if (isset($_SERVER['SERVER_NAME'])) {
            $subdomain = explode('.', $_SERVER['SERVER_NAME'])[0];
            if ($subdomain == 'laravel' || $subdomain == 'lvl') {
                return 'jasoncases';
            }
            return $subdomain;
        }
        return '';
    }

    public static function isDev()
    {
        return in_array(self::get(), self::$_devWhiteList);
    }

    public static function parseQueryString($queryString)
    {
        return (new static)->_pqs($queryString);
    }

    private function _pqs($qString)
    {
        $c = [];
        foreach (explode('&', $qString) as $param) {
            $kvp = explode('=', $param);
            if (!empty($kvp) && count($kvp) >= 1) {
                $c[$kvp[0]] = $kvp[1];
            }
        }
        return $c;
    }
}
