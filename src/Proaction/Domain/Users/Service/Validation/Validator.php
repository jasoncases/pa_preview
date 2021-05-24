<?php

namespace Proaction\Domain\Users\Service\Validation;

class Validator {
    private $email, $password, $pin;

    public function __construct($email = null, $password = null, $pin = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->pin = $pin;
    }

    public function process() {
        if (is_null($this->email) && is_null($this->password) && is_null($this->pin)) {
            throw new \Exception\VerificationMissing("No verification data provided");
        }

        if (!is_null($this->email) && is_null($this->password)) {
            throw new \Exception\VerificationMissing("Missing password.");
        }

        if (!is_null($this->email) && !is_null($this->password)) {
            return $this->_validateByEmail()->process($this->email, $this->password);
        }

        if (is_null($this->email) && is_null($this->password) && !is_null($this->pin)) {
            return $this->_validateByPin()->process($this->pin);
        }
    }

    private function _validateByEmail() {
        return new ValidateUserByEmail();
    }

    private function _validateByPin() {
        return new ValidateUserByPin();
    }
}
