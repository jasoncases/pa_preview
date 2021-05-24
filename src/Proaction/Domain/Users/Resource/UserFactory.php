<?php

namespace Proaction\Domain\Users\Resource;

class UserFactory {

    public static function create() {
        $userId = $_SESSION['user']['userId'] ?? null;
        if (is_null($userId)) {
            return NullUser::getInstance();
        } else {
            return EmployeeUser::getInstance();
        }
    }
}
