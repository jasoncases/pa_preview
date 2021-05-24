<?php

namespace Proaction\System\Controller;

use App\Http\Controllers\Controller;
use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\System\Resource\Auth\Auth;
use Proaction\System\Resource\Session\UserSession;

class LogoutController extends Controller {

    public function __invoke()
    {
        (new Auth(
            ProactionUser::where('id', (new UserSession())->pluck('userId'))->first()
            ))->logout();
        return redirect('/');
    }

}
