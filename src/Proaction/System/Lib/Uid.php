<?php

namespace Proaction\System\Lib;

class Uid
{

    private static $_prefix;
    private static $_symbol = '-';
    private static $_segLength;
    private static $_totalLength;

    public static function create($prefix, $segLength, $totalLength, $symbol = '-')
    {

        self::$_prefix = $prefix;
        self::$_segLength = $segLength;
        self::$_totalLength = $totalLength;
        self::$_symbol = $symbol;

        return self::init();
    }

    public static function init()
    {
        // append the prefix and return the formatted string
        return self::$_prefix . self::hypenate();
    }

    public static function hash()
    {
        // get hash of current microtime();
        return strtoupper(hash('sha256', (string) microtime()));
    }

    private static function parse()
    {

        $numOfSegs = floor(self::$_totalLength / self::$_segLength);

        $length = self::$_totalLength - strlen(self::$_prefix);
        $length -= $numOfSegs - 1;
        // walk through and add chars to the string up to totalLength
        $string = '';
        for ($ii = 0; $ii < $length; $ii++) {
            $string .= substr(self::hash(), $ii * 2, 1);
        }
        // return the working string
        return $string;
    }

    private static function hypenate()
    {
        // insert a symbol at each segLength
        return implode(self::$_symbol, str_split(self::parse(), self::$_segLength));
    }
}
