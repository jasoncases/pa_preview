<?php

namespace Proaction\System\Views;

use Proaction\System\Views\Base\BaseViewSystem;

class HomeIndexView extends BaseViewSystem
{

    protected $module = 'Home';

    protected function _getViewData()
    {
        return [
            'displayStatus' => 1,
            'statusMessage' => 'this is the status messsage',
            'isAdmin' => 1,
            'loggedIn' => 1,
            'clockedIn' => 1,
        ];
    }
}
