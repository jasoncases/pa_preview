<?php

namespace Proaction\System\Resource\Session;

use Proaction\System\Resource\Session\BaseSessionHandler;

class UserSession extends BaseSessionHandler
{
    protected $name = 'user';

    public function login($User)
    {
        $this->add('user_id', $User->getId());
        $this->add('email', $User->getEmail());
        $this->add('loggedIn', 1);
    }

    /**
     * Explicitly change the logged in value
     *
     * @return void
     */
    public function logout()
    {
        $this->add('loggedIn', 0);
        $this->remove('client');
    }

    public function clockIn()
    {
        $this->add('clockedIn', 1);
    }

    public function clockOut()
    {
        $this->add('clockedIn', 0);
    }
}
