<?php

namespace Proaction\Resource\Email\Routing;

use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\Domain\Clients\Model\Client;

class SubRouter
{
    protected $client;

    protected $emailParser;
    protected $domain = '@zerodock.com';
    protected $alertSender = false;

    public function __construct($emailParser)
    {
        $this->emailParser = $emailParser;
    }

    protected function _init()
    {
        try {
            $this->_setOptions();
            $this->_extendInit();
        } catch (\Exception $e) {
            if ($this->alertSender) {
                //
            }
            mail(ProactionUser::defaultAdminEmail(), 'Error with email routing', print_r(debug_backtrace(), true));
        }
    }

    private function _setOptions()
    {
        // here we will set alertSender from globals
    }

    protected function _parseTo($to)
    {
    }

    protected function _extendInit()
    {
    }

    protected function _getClientByPrefix($prefix)
    {
        return Client::where('client_system_prefix', $prefix)->get(
            'client_system_prefix',
            'uid',
        );
    }
}
