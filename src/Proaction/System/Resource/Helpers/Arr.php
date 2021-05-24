<?php

namespace Proaction\System\Resource\Helpers;

class Arr
{
    public static function retMulti($array, $col = 'id')
    {
        if (is_null($array)) {
            return null;
        }
        return empty(array_count_values(array_column($array, $col))) ? [$array] : $array;
    }

    public static function flatten($array = [])
    {
        // short circuit if there is no value
        if (is_null($array)) {
            return null;
        }

        // if not array, make a single val array and return it
        if (!is_array($array)) {
            return [$array];
        }

        return self::returnFlatArray($array);
    }

    private static function returnFlatArray($array)
    {
        $c = [];
        foreach ($array as $val) {
            if (!is_array($val)) {
                $c[] = $val;
            } else {
                $c[] = current($val);
            }
        }
        return $c;
    }

    public static function pre($array)
    {
        echo '<pre>';
        if (isset($_GET['locate'])) {
            print_r($_SERVER);
        }
        if (is_null($array)) {
            echo "<h3>Arr::pre() -> Provided value is null</h3>";
        }
        if ($array == false) {
            echo "<h3>Arr::pre() -> Provided value is false</h3>";
        }
        print_r($array);
        echo '</pre>';
    }

    public static function sort($array, $column, $dir = SORT_ASC)
    {
        try {
            if (empty($array)) {
                return $array;
            }
            array_multisort(array_column($array, $column), $dir, $array);
            return $array;
        } catch (\Exception $e) {
            echo '<h3>Arr::sort error</h3>';
            echo "<p>column: $column</p>";
            echo "<p>dir: $dir</p>";
            Arr::pre($array);
        }
    }

    public static function sanitize($array, $allowedKeys) {
        foreach ($array as $key => $value) {
            if (!in_array($key, $allowedKeys)) {
                unset($array[$key]);
            }
        }
        return $array;
    }
}
