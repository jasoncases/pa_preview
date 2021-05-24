<?php

namespace Proaction\System\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Proaction\Domain\Users\Model\ForcePasswordReset;
use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\Domain\Users\Service\Validation\UserIdentity;
use Proaction\System\Resource\Auth\Auth;
use Proaction\System\Resource\Json\Jsonify;
use Proaction\System\Resource\Status\Status;

/**
 * Login endpoint. 
 * 
 * Accepts an array of login data. 
 *      [
 *          'email' => x,
 *          'pass'  => x,
 *          'pin'   => x,
 *      ]
 * 
 * UserIdentity takes the 3 values and will determine the proper method
 * to use depending on the values given. 
 */
class LoginController extends Controller
{

    public function __invoke(Request $req)
    {

        // validate the login array provided
        $login = UserIdentity::validate($req->input('email'), $req->input('pass'), $req->input('pin'));

        return $this->_processLogin($login);
    }

    /**
     * Process the login.
     *
     * @param array $login
     * @return string
     */
    private function _processLogin($login)
    {
        // check status of login
        if ($login['status'] == 'success') {

            // Check if user has a forced password reset
            if (ForcePasswordReset::exists($login['user_id'])) {
                return $this->_forcedPasswordRedirect($login['user_id']);
            }

            // return successful auth login
            return $this->_runAfterSuccessfulLogin($login);
        }

        // on failure, return error status
        return (new Status())->error();
    }

    private function _runAfterSuccessfulLogin($login)
    {
        // define the user by the returned user_id value
        $user = ProactionUser::where('id', $login['user_id'])->get()->first();
        // pass the recieved user model to Auth and log to session
        return (new Auth($user))->loginByUser();
    }

    /**
     * Return login status of success, while telling the login script to 
     * redirect the user to a forced reset page.
     *
     * @param int $user_id
     * @return void
     */
    private function _forcedPasswordRedirect($user_id)
    {
        return Jsonify::go(['status' => 'success', 'forcedPasswordReset' => true, 'user_id' => $user_id]);
    }
}
