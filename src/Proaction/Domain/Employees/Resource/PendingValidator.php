<?php

namespace Proaction\Domain\Employees\Resource;

use Exception;
use Proaction\Domain\Clients\Model\GlobalSetting;
use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Employees\Model\PendingEmployee;
use Proaction\System\Resource\Regex\RegexHandler;

class PendingValidator
{

    /**
     * Keys that map to methods to validate properites of the incoming
     * array of PendingEmployee props.
     *
     * To validate addition properites, add a key that matches the key/
     * field name and a method to do the validation work
     *
     * @var array
     */
    private $validateKeys = [
        'email' => '_validateEmail',
        'pin' => '_validatePin',
        'phone' => '_validatePhone',
    ];

    public static function validate(array $props)
    {
        return (new static)->_validate($props);
    }

    /**
     * Undocumented function
     *
     * @param array $props
     * @return void
     */
    private function _validate($props)
    {
        foreach ($this->validateKeys as $key => $m) {
            if (array_key_exists($key, $props)) {
                $this->{$this->validateKeys[$key]}($props[$key], $props['id'] ?? null);
            }
        }
    }

    private function _validateEmail($email, $pendingId = null)
    {
        if (!is_null($pendingId)) {
            if (!PendingEmployee::emailIsUnique($email, $pendingId)) {
                throw new Exception('A pending employee record exists with this email - All emails must be unique');
            }
        }

        if (!RegexHandler::isEmail($email)) {
            throw new Exception('Incorrect email format - please provide a correctly formatted email');
        }

        if (!Employee::emailIsUnique($email)) {
            throw new Exception('Non-unique email given - User emails must be unique');
        }
    }

    private function _validatePin($pin, $pendingId = null)
    {
        if (!is_null($pendingId)) {
            if (!PendingEmployee::pinIsUnique($pin, $pendingId)) {
                throw new Exception('A pending employee record exists with this pin - All pins must be unique');
            }
        }

        if (!RegexHandler::isPin($pin)) {
            throw new Exception('Incorrect pin format give - Please provide a (' . GlobalSetting::get('pin_length') . ') digit numeric pin');
        }

        if (!Employee::pinIsUnique($pin)) {
            throw new Exception('Non-unique pin given - User pins must be unique for each user');
        }
    }

    private function _validatePhone($phone, $pendingId = null)
    {

        if (!is_null($pendingId)) {
            if (!PendingEmployee::phoneIsUnique($phone, $pendingId)) {
                throw new Exception('A pending employee record exists with this phone number - All phone numbers must be unique');
            }
        }


        if (!Employee::phoneIsUnique($phone)) {
            throw new Exception('Non-unique phone number given - User phone numbers must be unique');
        }
    }
}
