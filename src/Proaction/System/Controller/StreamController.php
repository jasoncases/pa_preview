<?php

namespace Proaction\System\Controller;

use Proaction\System\Clients\Resource\ProactionClient;
use Proaction\System\Resource\Cache\Cache;
use Proaction\System\Resource\Cache\ProactionRedis;

abstract class StreamController extends BaseApiProactionController {

    public function __invoke()
    {
        return response()->stream(function() {

            return $this->_getStream();

        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
        ]);
    }

    protected abstract function _getStream();
}
