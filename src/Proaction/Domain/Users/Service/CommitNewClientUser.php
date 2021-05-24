<?php

namespace Proaction\Domain\Users\Service;

use Proaction\Domain\Users\Resource\Create;
use Proaction\System\Lib\Uid;

class CommitNewClientUser
{
    private $userObj;

    public function __construct($userObj)
    {
        $this->userObj = $userObj;
    }

    public function commit()
    {
        $pass = $this->_createTemporaryPassword();
        return Create::newUser(
            $this->userObj->email,
            $pass,
            $this->userObj->pin
        );
    }

    private function _createTemporaryPassword()
    {
        $symbol = ['!', '@', '$'][rand(0, 2)];
        $lead = ['H', 'Z', 'T', 'J', 'Q'][rand(0, 4)];
        return Uid::create("$symbol$lead", 10, 10, '');
    }
}
