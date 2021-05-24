<?php

namespace Proaction\Domain\Users\Resource;

class ProactionUserFactory
{

    public static function create()
    {
        $userId = $_SESSION['user']['userId'] ?? null;
        if (is_null($userId)) {
            return NullUser::getInstance()->create();
        } else {
            return EmployeeUser::getInstance()->create($userId);
        }
    }
}
