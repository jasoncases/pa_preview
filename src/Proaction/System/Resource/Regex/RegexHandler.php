<?php

namespace Proaction\System\Resource\Regex;

use Proaction\Domain\Clients\Model\GlobalSetting;

/**
 * Handles all regex checks for the application
 */
class RegexHandler
{
    public function __construct()
    {
    }

    /**
     * Validate Timestamp YYYY-MM-DD HH:II:SS
     *
     * @param string $string
     * @return bool
     */
    public static function isTimestamp($string)
    {
        $re = '/[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]/';
        return preg_match($re, $string);
    }

    /**
     * Validate Datestamp YYYY-MM-DD
     *
     * @param string $string
     * @return bool
     */
    public static function isDateStamp($string)
    {
        $re = '/[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/';
        return preg_match($re, $string);
    }
    /**
     * Validate email
     *
     * @param string $string
     * @return bool
     */
    public static function isEmail($string)
    {
        $email = filter_var($string, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate password
     *
     * @param string $string;
     * @return bool
     */
    public static function isPassword($string)
    {
        // define minimum password length
        $MINPASSLENGTH = 8;

        // set the regex rules
        $rules = [
            'uppercase' => preg_match('@[A-Z]@', $string),
            // 'lowercase' => preg_match('@[a-z]@', $string),
            'number' => preg_match('@[0-9]@', $string),
            // 'special'   => preg_match('@[!\@\#\$\&]@', $string),
            'length' => strlen($string) >= $MINPASSLENGTH,
        ];

        // error messages on failure
        $errorMessage = [
            'uppercase' => 'at least one (1) uppercase character.',
            'lowercase' => 'at least one (1) lowercase character.',
            'number' => 'at least one (1) number.',
            // 'special'   => 'at least one (1) special character.',
            'length' => 'at least eight (8) characters.',
        ];

        try {

            // ensure string is not empty
            if (is_null($string)) {
                throw new \Exception\IllegalValueException('Password must not be empty.');
            }
            // loop through the rules. If any fail, throw an exception
            foreach ($rules as $k => $v) {
                if (!$v) {
                    throw new \Exception\IllegalValueException('<br />Password must contain ' . $errorMessage[$k]);
                }
            }

            // return true if all rules pass
            return true;
        } catch (\Exception $e) {
            echo $e->getMessage();
            die();
        }

        return false;
    }

    /**
     * Validate Pincode
     *
     * @param string $string
     * @return bool
     */
    public static function isPin($string)
    {
        // define minimum pin length
        $MINPINLEN = GlobalSetting::get('pin_length');

        // set the regex rules
        $rules = [
            'alpha' => !preg_match('@[A-Za-z]@', $string),
            'number' => preg_match('/[0-9]/', $string),
            'length' => strlen($string) == $MINPINLEN,
        ];

        try {

            // ensure string is not empty
            if (is_null($string)) {
                return false;
            }

            // loop through the rules. If any fail, throw an exception
            foreach ($rules as $k => $v) {
                if (!$v) {
                    return false;
                }
            }

            // return true if all rules pass
            return true;
        } catch (\Exception\RegexPin $e) {
            // die($e->getMessage());
        }

        return false;
    }

    /**
     *
     */
    public static function isBool($value)
    {
        if ($value === false) return true; // edge case, an explicit *false* declaration fails the preg_match
        return preg_match('/\'?true\'?|\'?false\'?|1|0/', $value);
    }

    /**
     *
     */
    public static function isString($value)
    {
        return gettype($value) == 'string';
    }

    /**
     *
     */
    public static function isInteger($value)
    {
        return preg_match('/^[0-9]+$/', $value);
    }

    /**
     *
     */
    public static function isArray($value)
    {
        return is_array($value) || is_object($value);
    }

    /**
     *
     */
    public static function isObject($value)
    {
        return is_array($value) || is_object($value);
    }

    /**
     *
     */
    public static function isFloat($value)
    {
        return gettype($value) == 'double';
    }
}
