<?php

namespace Proaction\Resource;

class Token
{
    public static function create()
    {
        return md5(uniqid(rand(), true));
    }
}