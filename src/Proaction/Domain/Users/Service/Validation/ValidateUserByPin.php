<?php

namespace Proaction\Domain\Users\Service\Validation;

use Proaction\Domain\Users\Model\Pin;
use Proaction\System\Resource\Helpers\Arr;

class ValidateUserByPin{

    public function process($base64EncodedPin)
    {
        $user = $this->_pin($base64EncodedPin)->getUserByPin();
        if (is_null($user)) {
            return ['status' => 'error', 'message' => 'Incorrect PIN Access Code.'];
        }
        return ['status' => 'success', 'user_id' => $user->id];
    }

    private function _pin($base64EncodedPin){
        $pinModel = new Pin();
        $pinModel->setPin($base64EncodedPin);
        return $pinModel;
    }
}
