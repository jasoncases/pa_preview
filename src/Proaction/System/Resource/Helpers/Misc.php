<?php

namespace Proaction\System\Resource\Helpers;

use Proaction\Domain\Clients\Model\GlobalSetting;

/**
 * A container class for miscellaneous helpers
 */
class Misc
{

    /**
     * Returns true if a string is JSON
     *
     * @param string $str
     * @return boolean
     */
    public static function isJson($str)
    {
        try {
            json_decode($str);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Return default USD money format. I got sick of typing out
     * number_format(x, y, z, aa)
     *
     * @param string $dollar
     * @return string
     */
    public static function money($dollar)
    {
        return number_format($dollar, 2, '.', '');
    }

    /**
     * Return a timestamp to float
     *
     * @param string $timestampString - MUST BE `Y-m-d H:i:s` format
     * @return float
     */
    public static function stampToFloat($timestampString)
    {
        /**
         * This regex does not match any values, so an invalid timestamp
         * can still be sent through, this is just checking for format
         * and *length* of values
         */
        if (!preg_match(
            '/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/',
            $timestampString
        )) {
            throw new \Exception(
                "Provided timestamp does not match required pattern:
                YYYY-MM-DD HH:MM:SS,   use php format [`Y-m-d H:i:s`]"
            );
        }
        $parts = explode(':', date("H:i:s", strtotime($timestampString)));
        return Misc::money(
            intval($parts[0]) + floor((intval($parts[1]) / 60) * 100) / 100
        );
    }

    /**
     * Given a date, return the beginning and end date range as an array.
     *
     * This array can then be used to pull shifts and activities from the
     * database
     *
     * @param string $date defaults to current date
     * @return array
     */
    public static function getPayrollDateRange($date = null)
    {
        // handle null case to current day and get unix seconds
        if (is_null($date)) {
            $date = date('Y-m-d');
        } else if (gettype($date) === 'integer') {
            $date = date('Y-m-d', $date);
        }
        $datetime_unix = strtotime($date);

        // get the client specific week start value
        $start = GlobalSetting::get('schedule_week_start');

        // local variables, dowProvided is the day of week of the provided
        // date. Max day creates an offset based on the start day value
        $dowProvided = date("w", $datetime_unix);
        $maxDay = 6 + $start;

        // get the difference between the dowprovided and the start day
        $diffStart = $dowProvided - $start;
        // end is the maxday offset diff from dow provided
        $diffEnd = $maxDay - $dowProvided;

        // return the array results
        return [
            date("Y-m-d 00:00:00", strtotime("-$diffStart days", $datetime_unix)),
            date("Y-m-d 23:59:59", strtotime("+$diffEnd days", $datetime_unix)),
        ];
    }
}
