<?php

namespace Proaction\System\Controller;

use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Session\SystemSession;
use Proaction\System\Resource\Status\FlashAlert;
use Proaction\System\Resource\Status\Status;

/**
 * An api endpoint for the TypeScript components.
 *
 * TODO - Add a check for 'allowedRoutes' ------------------------------
 *        - The intent of checking allowed routes allows us to define
 *        - which routes can see the response, most likely using the
 *        - HTTP_REFERER value in $_SERVER. This, along with the defined
 *        - expiration time, should eliminate the message leakage we saw
 */
class FlashAlertController extends BaseApiProactionController
{

    public function __invoke()
    {
        // creation session pointer
        $systemSession = new SystemSession();

        // look for a response
        $response = $systemSession->pluck('response');

        // output response or error
        if ($response) {
            // check that the message is still fresh
            if (!$this->_isExpired($response)) {
                // send the successful message
                (new Status())->aux($response)->echo();
            } else {
                // send an empty successful response, by sending the
                // response and the current server time for comparison
                // We send the expiredResponse, so that it could still
                // be handled in the event that the preset expiration
                // should be ignored
                // TODO - REMOVE server, only in for some testing and
                // TODO - diagnostics
                (new Status())->aux(['server' => $_SERVER, 'expiredResponse' => $response, 'now' => round((microtime(true)) * 1000)])->echo();
            }
        } else {

            (new Status())->aux(['message' => 'No response provided'])->error();
        }

        // clear the response from the user's session
        $systemSession->remove('response');
    }

    /**
     * check that the expiration hasn't passed
     *
     * @param [type] $response
     * @return void
     */
    private function _isExpired($response)
    {
        $responseExpiration = $response['flashAlert']['expiration'];
        if (is_null($responseExpiration)) {
            return false;
        }
        $nowWithMs = round((microtime(true)) * 1000);
        return $nowWithMs > $responseExpiration;
    }
}
