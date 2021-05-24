<?php

namespace Proaction\Domain\Users\Service;

use Proaction\Domain\Clients\Resource\ProactionClient;
use Proaction\Domain\Users\Model\ForcePasswordReset;
use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\Domain\Users\Resource\Create;
use Proaction\System\Resource\Email\Email;
use Proaction\System\Resource\Email\Template\EmailTemplate;
use Proaction\System\Resource\Lib\Uid;
use Proaction\System\Resource\Templater\Templater;

class UpdateUserPassword
{
    private $employee;
    public function __construct($employeeObj)
    {
        $this->employee = $employeeObj;
    }

    /**
     * Manual update user password
     *
     * @param string $password
     * @return bool
     */
    public function update($password)
    {
        $this->_clearForcedResetRecord($this->employee['user_id']);
        return $this->_updateUserRecord($this->employee['user_id'], $password);
    }


    private function _clearForcedResetRecord($user_id)
    {
        $recordId = ForcePasswordReset::where('user_id', $user_id)->latest('id')->limit(1)->get('id');
        return ForcePasswordReset::destroy($recordId);
    }

    /**
     * Sets temporary password, emails temp to employee and sets force
     * reset
     *
     * @return void
     */
    public function setTemporary()
    {
        $this->_setTemporaryPassword();
    }

    private function _setTemporaryPassword()
    {
        $password = $this->_createTemporaryPassword();
        $this->_updateUserRecord($this->employee['user_id'], $password);
        $this->_createForcePasswordReset();
        $this->_sendTemporaryPasswordEmail($password);
    }

    private function _sendTemporaryPasswordEmail($password)
    {
        $client_prefix = ProactionClient::prefix();
        $email = $this->employee['email'];
        return Email::to($email)
            ->subject('Proaction - Password Reset Request')
            ->message(
                Templater::parse(
                    EmailTemplate::load('employee/temporary_password'),
                    compact('email', 'password', 'client_prefix')
                )
            )
            ->compose();
    }

    private function _updateUserRecord($id, $password)
    {
        $password = Create::hashPassword($password);
        return ProactionUser::p_update(compact('id', 'password'));
    }

    private function _createForcePasswordReset()
    {
        // If the user already has a forced reset record, we can skip
        // creation of a new one, as it will force the user to reset 2x
        $forcedResetRecordExists = ForcePasswordReset::exists('user_id', $this->employee['user_id']);
        return $forcedResetRecordExists ?? ForcePasswordReset::p_create([
            'user_id' => $this->employee['user_id'],
        ]);
    }

    private function _createTemporaryPassword()
    {
        $symbol = ['!', '@', '$'][rand(0, 2)];
        $lead = ['H', 'Z', 'T', 'J', 'Q'][rand(0, 4)];
        return Uid::create("$symbol$lead", 14, 14, '');
    }
}
