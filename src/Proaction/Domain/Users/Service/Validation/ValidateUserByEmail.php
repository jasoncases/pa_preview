<?php

namespace Proaction\Domain\Users\Service\Validation;

use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\System\Resource\Helpers\Arr;

class ValidateUserByEmail
{

    public function process($email, $password)
    {
        $res = $this->_getHashObj($email);
        if (!$res) {
            return ['status' => 'error', 'message' => 'Incorrect credentials, user not found'];
        }
        if (password_verify($password, $res->password)) {
            return ['status' => 'success', 'user_id' => $res->user_id];
        }
        return ['status' => 'error', 'message' => 'Incorrect credentials, user not found'];
    }

    private function _getHashObj($email)
    {
        return ProactionUser::where('email', $email)->first(['password', 'id as user_id']);
    }
}
