<?php

namespace Proaction\System\Resource\Token;

class Token
{
    public static function create()
    {
        return md5(uniqid(rand(), true));
    }
}
