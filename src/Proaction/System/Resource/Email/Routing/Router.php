<?php

namespace Proaction\Resource\Email\Routing;

use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\Resource\Email\Routing\TicketReply;

class Router
{

    private $re_error = '/(SMTP error from remote mail server|email account that you tried to reach is disabled)/';

    /**
     *
     *
     * */
    public function __construct($email)
    {

        $this->_validateIncomingEmail($email);

        $this->_route($email);
    }

    private function _route($email)
    {
        $match = '/^To: (.*)/';
        $to = current(
            array_filter(
                $email,
                function ($v, $k) use ($match) {
                    return preg_match($match, $v, $matches);
                },
                ARRAY_FILTER_USE_BOTH
            )
        );
        $to = trim(str_replace('to:', '', strtolower($to)));
        return RouteFactory::create($to, $email);
    }

    private function _validateIncomingEmail($email)
    {
        if ($this->_emailContainsSuspectedErrorText($email)) {
            mail(ProactionUser::defaultAdminEmail(), 'Proaction: Attention: Email Flagged For Error', print_r($email, true));
            exit();
        }
    }

    private function _emailContainsSuspectedErrorText($email)
    {
        return preg_match($this->re_error, implode(' ', $email));
    }
}
