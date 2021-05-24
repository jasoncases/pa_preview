<?php

namespace Proaction\System\Controller;

use Proaction\System\Views\GeneralView;

class EmailLoginController extends BaseApiProactionController
{

    public function __invoke()
    {
        return view('System.Home.login', GeneralView::add());
    }
}
