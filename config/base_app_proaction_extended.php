<?php

/*
|--------------------------------------------------------------------------
| Proaction Application Extensions
|--------------------------------------------------------------------------
|
| We are using this as a place to create top-level functions that can be
| used throught the application and/or need to be created in the config
| step
|
*/

define("DEFAULT_ACCT", 'jasoncases');

if (!function_exists('credentials')) {

    /**
     * Access the Client `.credentials` file and return values for db
     * access
     *
     * @param string $prefix
     * @param string $key
     * @return void
     */
    function credentials($prefix, $key)
    {
        $prefix = boolval($prefix) ? $prefix : DEFAULT_ACCT;
        $path = "/home/zerodock/proaction_clients/$prefix/.credentials";

        if (!file_exists($path)) {
            throw new \Exception("Missing file $path");
        }

        $handle = fopen($path, 'r');
        if ($handle) {
            $key = trim($key);
            // loop through each line
            while (($line = fgets($handle)) !== false) {

                // if the beginning of the line, for the length of the
                // key (+1) matches the padded $varName...
                if (substr($line, 0, strlen($key)) == $key) {
                    // break the line at the = and return the value
                    $val = trim(explode('=', $line)[1]);
                    fclose($handle); // close the handle
                    return $val; // return the value
                }
            }
        }
        // close handle and return null
        fclose($handle);
        return;
    }
}
