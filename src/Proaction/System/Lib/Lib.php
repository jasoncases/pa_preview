<?php

namespace Proaction\System\Lib;

use Proaction\Domain\Clients\Model\GlobalSetting;
use Proaction\Domain\Tasks\Model\PerpetualTask;
use Proaction\Domain\Tasks\Model\Task;
use Proaction\System\Resource\Config\ProactionConfig;
use Proaction\System\Resource\Templater\Templater;

/**
 * Given a date, return the beginning and end date range as an array.
 *
 * This array can then be used to pull shifts and activities from the
 * database
 *
 * @param string $date defaults to current date
 * @return array
 */
function getPayrollDateRange($date = null)
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


function calc($val1, $operator, $val2)
{
    return calcOp($val1, $operator, $val2);
}


function calcOp($val1, $operator, $val2)
{
    switch ($operator) {
        case '==':
            return $val1 == $val2;
        case '!=':
            return $val1 != $val2;
        case '<':
            return $val1 < $val2;
        case '<=':
            return $val1 <= $val2;
        case '>':
            return $val1 > $val2;
        case '>=':
            return $val1 >= $val2;
    }
}

function buildPath($path)
{
    return Templater::parse($path, ProactionConfig::all());
}

function money($dollar)
{
    return number_format($dollar, 2, '.', '');
}

function showDevBanner()
{
    echo '<span class="dev-alert">' . explode('.', $_SERVER['SERVER_NAME'])[0] . '</span>';
}

function nextTaskId()
{
    return max([PerpetualTask::lastInsertId(), Task::lastInsertId()]) + 1;
}

function strIsJson($str)
{
    try {
        json_decode($str);
        return true;
    } catch (\Exception $e) {
        return false;
    }
}
