<?php

namespace Proaction\Domain\Users\Controller;

use Proaction\System\Controller\BaseApiProactionController;
use Proaction\System\Views\GeneralView;

class PasswordRecoveryController extends BaseApiProactionController
{

    public function index()
    {
        return view('Domain.Users.PasswordRecovery.index', GeneralView::add());
    }
}
