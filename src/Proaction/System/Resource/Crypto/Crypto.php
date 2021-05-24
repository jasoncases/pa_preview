<?php

namespace Proaction\System\Resource\Crypto;

use Proaction\System\Resource\Config\DotEnv;

class Crypto
{

    private static $defaultMethod = 'CIPHER_METHOD';

    /**
     * Undocumented function
     *
     * @param string $string
     * @param string $key
     * @param string $method
     * @return string
     */
    public static function encrypt($string, $key, $method=null)
    {
        // get key and method from .env
        if (is_null($method)) {
            $method = self::$defaultMethod;
        }

        $_method = DotEnv::get($method);

        // define nonceSize and nonce value
        $nonceSize = openssl_cipher_iv_length($_method);
        $nonce = openssl_random_pseudo_bytes($nonceSize);

        // encrypt the supplied string
        $cipherText = openssl_encrypt($string, $_method, $key, OPENSSL_RAW_DATA, $nonce);

        // return the encrypted string with the nonce prefix attached
        // and base64 encode it
        return base64_encode($nonce . $cipherText);
    }

    /**
     * Undocumented function
     *
     * @param string $cipher
     * @param string $key
     * @param string $method
     * @return string
     */
    public static function decrypt($cipher, $key, $method=null)
    {

         // get key and method from .env
        if (is_null($method)) {
            $method = self::$defaultMethod;
        }

        $_method = DotEnv::get($method);

        // decode from base64
        $message = base64_decode($cipher, true);

        // define noncesize from _method
        $nonceSize = openssl_cipher_iv_length($_method);

        // break the nonce and ciphertext values
        $nonce = mb_substr($message, 0, $nonceSize, '8bit');
        $ciphertext = mb_substr($message, $nonceSize, null, '8bit');

        // decrypt
        $plainText = openssl_decrypt($ciphertext, $_method, $key, OPENSSL_RAW_DATA, $nonce);

        // return
        return $plainText;
    }
}
