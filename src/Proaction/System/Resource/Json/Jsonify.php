<?php

namespace Proaction\System\Resource\Json;

class Jsonify {

    public static function go($key, $value = null) {
        if (is_array($key)) {
            // handle array of values
            return json_encode($key);
        }
        return json_encode([$key => $value]);
    }
}
