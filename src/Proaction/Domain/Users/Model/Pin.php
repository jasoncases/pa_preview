<?php

namespace Proaction\Domain\Users\Model;

use Proaction\Domain\Employees\Model\Employee;
use Proaction\System\Model\ClientModel;
use Proaction\System\Resource\Config\DotEnv;
use Proaction\System\Resource\Helpers\Arr;

class Pin extends ClientModel
{

    protected $table = 'users_pin';
    // protected $autoColumns = ['_ip'];

    protected $identifier = 'id';


    public static function saveAndMask($user_id, $email, $pin)
    {
        return (new static)->_saveAndMask($user_id, $email, $pin);
    }


    private function _saveAndMask($user_id, $email, $pin)
    {
        $email_mask = hash('sha512', $email);
        $pin_mask = $this->hashPin($pin);
        return Pin::p_create(compact('user_id', 'email_mask', 'pin_mask'));
    }
    /**
     * Undocumented function
     *
     * @param string $pin
     * @return void
     */
    public function setPin($pin)
    {
        $this->_pin = base64_decode($pin);

        return $this;
    }

    public function getUserByPin()
    {
        return $this->_runComparison();
    }

    private function _runComparison()
    {
        $user = $this->_getUserIdByPin();
        if (is_null($user) || empty($user)) {
            return null;
        }
        return ProactionUser::where('id', $user->user_id)->get('id', 'status', 'last_login')->first();
    }

    private function _getUserIdByPin()
    {
        $pin_mask = $this->hashPin($this->_pin);
        return self::where('pin_mask', $pin_mask)->where('status', 1)->first('user_id');
    }

    public function hashPin(string $pin): string
    {
        return hash('sha3-256', $this->_salt() . $pin);
    }

    private function _salt()
    {
        return DotEnv::get('PIN_SALT');
    }

    /**
     * Undocumented function
     *
     * @param [type] $pin
     * @return boolean
     */
    public static function isUnique($pin)
    {
        return (new static)->_pinIsUnique($pin);
    }

    /**
     *
     */
    public function _pinIsUnique($pin)
    {
        return !boolval(Pin::where('pin_mask', $this->hashPin($pin))->where('status', 1)->first());
    }

    public static function updateUserPin($user_id, $pin)
    {
        return (new static)->_updateUserPin($user_id, $pin);
    }

    private function _updateUserPin($user_id, $pin)
    {
        $id = Pin::where('user_id', $user_id)->get('id');
        $pin_mask = $this->hashPin($pin);
        return Pin::p_update(compact('id', 'pin_mask'));
    }

    public static function updateEmployeePin($employee_id, $pin)
    {
        $user_id = Employee::find($employee_id, ['user_id']);
        if (!$user_id) {
            throw new \Exception('User not found');
        }
        return Pin::updateUserPin($user_id, $pin);
    }
}
