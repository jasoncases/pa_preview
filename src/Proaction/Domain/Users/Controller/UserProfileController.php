<?php

namespace Proaction\Domain\Users\Controller;

use Proaction\System\Controller\BaseProactionController;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Views\GeneralView;

class UserProfileController extends BaseProactionController
{

    public function __invoke($id)
    {
        return view('Domain.Users.profile', GeneralView::add());
    }
}
