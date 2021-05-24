<?php

namespace Proaction\System\Resource\Status;

use Proaction\System\Resource\Session\SystemSession;

/**
 * Allow the server to set a message for the client side to see a "flash
 * alert".
 */
class FlashAlert
{
    private $array;

    private $session;

    // time to live for the flash alert
    private $ttl = 5000;

    public function __construct($message, $status = 'success', $allowedRoutes = [], $ttl = null)
    {
        $this->session = new SystemSession();

        if (is_array($status)) {
            $ttl = is_array($allowedRoutes) ? null : $allowedRoutes;
            $allowedRoutes = $status;
            $status = 'success';
        }

        $status = strtolower($status) == 'error' ? 'danger' : $status;

        $timeToLive = $ttl ?? $this->ttl;

        $this->array['status'] = strtolower($status);
        $this->array['message'] = $message;
        $this->array['allowedRoutes'] = $allowedRoutes;
        $this->array['expiration'] = round((microtime(true)) * 1000) + $timeToLive;

        if (strtolower($status) != 'success') {
        }

        $this->session->add('response', $this->encode());
    }

    private function encode()
    {
        return ['flashAlert' => $this->array];
    }
}
