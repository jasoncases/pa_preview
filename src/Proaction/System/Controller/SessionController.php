<?php

namespace Proaction\System\Controller;

use Proaction\System\Resource\Helpers\Arr;

class SessionController extends BaseApiProactionController {

    public function read() {
        echo json_encode($_SESSION, JSON_PRETTY_PRINT);
    }

    public function postToSession() {
        echo json_encode(["aintthis" => "someshit"]);
    }

}
