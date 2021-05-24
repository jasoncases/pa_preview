<?php

namespace Proaction\Domain\Users\Service\Validation;

class UserIdentity{

    public static function validate($email=null, $password=null, $pin=null) {
        return (new static)->_validate($email, $password, $pin)->process();
    }

    private function _validate($email=null, $password=null, $pin=null) {
        return new Validator($email, $password, $pin);
    }

}
