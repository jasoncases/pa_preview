<?php

namespace Proaction\Domain\Users\Resource;

use Proaction\Domain\Clients\Resource\ProactionClient;
use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Users\Model\ForcePasswordReset;
use Proaction\Domain\Users\Model\Pin;
use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\System\Resource\Email\Email;
use Proaction\System\Resource\Regex\RegexHandler;

class Create
{
    private static $passOptions = ['cost' => 12];

    public static function newUser($email, $password, $pin = null)
    {
        return (new static)->_createNewUser($email, $password, $pin);
    }

    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT, Create::$passOptions);
    }

    private function _createNewUser($email, $password, $pin)
    {
        try {
            $this->_verifyInput($email, $password, $pin);
            $user = $this->_insertUser($email, $password);
            $this->_insertForcePasswordResetState($user->id);
            $this->_insertUserPin($user->id, $email, $pin);
            $this->_alertUserAndAdminOfAccountCreation($email, $password, $pin);
            return $user->id;
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    private function _insertUser($email, $password)
    {
        $registered = time();
        $password = Create::hashPassword($password);
        return ProactionUser::p_create(compact('email', 'password', 'registered'));
    }

    private function _insertUserPin($user_id, $email, $pin)
    {
        if (!is_null($pin)) {
            return Pin::saveAndMask($user_id, $email, $pin);
        }
    }

    private function _verifyInput($email, $password, $pin)
    {
        $this->_verifyEmail($email);
        $this->_verifyPassword($password);
        $this->_verifyPin($pin);
    }

    private function _verifyEmail($email)
    {
        if (!RegexHandler::isEmail($email)) {
            throw new \Exception('incorrect email format');
        }
        if (!Employee::emailIsUnique($email)) {
            throw new \Exception('User email value must be unique. Duplicate found.');
        }

        // ! at this point, the meta user was created by this loop
        // if (!ProactionUser::emailIsUnique($email)) {
        //     throw new \Exception\DatabaseDuplicateEntry('User email value must be unique. Duplicate found.');
        // }
    }

    private function _verifyPassword($password)
    {
        if (!RegexHandler::isPassword($password)) {
            throw new \Exception\RegexPassword();
        }
    }

    private function _verifyPin($pin)
    {
        if (!is_null($pin)) {
            if (!RegexHandler::isPin($pin)) {
                throw new \Exception('Incorrect pin format.');
            }
            if (!Pin::isUnique($pin)) {
                throw new \Exception('PIN codes must be unique. Duplicate found.');
            }
        }
    }

    private function _insertForcePasswordResetState($user_id)
    {
        return ForcePasswordReset::p_create(compact('user_id'));
    }

    private function _alertUserAndAdminOfAccountCreation($email, $password, $pin)
    {
        Email::to(ProactionUser::defaultAdminEmail())
            ->subject('Proaction - New User Created')
            ->addCC(ProactionUser::defaultAdminEmail())
            ->addCC('jason@jasoncases.com')
            ->message(
                $this->_buildMessage($email, $password, $pin)
            )
            ->compose();
    }

    private function _buildMessage($email, $password, $pin)
    {
        return view('Domain.Employees.Pending.creation_email', [
            'client_prefix' => ProactionClient::prefix(),
            'email' => $email,
            'password' => $password,
            'pin' => $pin,
        ])->render();
    }
}
