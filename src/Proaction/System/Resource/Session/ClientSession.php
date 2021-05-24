<?php

namespace Proaction\System\Resource\Session;

class ClientSession extends BaseSessionHandler
{
    protected $name = 'client';
    public function __construct()
    {
        parent::__construct();
    }

    public function destroy() {
        $this->add('loggedIn', 0);
        $this->remove('uid');
        $this->remove('prefix');
        $this->remove('initialized');
        $this->remove('modules');
        return true;
    }
}
