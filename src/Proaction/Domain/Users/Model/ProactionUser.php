<?php

namespace Proaction\Domain\Users\Model;

use Proaction\System\Model\ClientModel;
use Proaction\Domain\Users\Resource\Create;

class ProactionUser extends ClientModel
{

    protected $table = 'users';
    protected $pdoName = 'client';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * This is the default system administrator email. 
     * 
     * For now it's just a string with my email address, but it can be 
     * changed to actually query the database for a real value, we just
     * haven't needed to do that yet
     *
     * @return string
     */
    public static function defaultAdminEmail()
    {
        return self::defaultAdminEmail();
    }

    public static function emailIsUnique($email)
    {
        return !boolval(
            self::where('email', $email)->where('status', 1)->get()
        );
    }

    public static function updatePassword(string $email, string $password)
    {
        return (new static)->_updateUserPassword($email, $password);
    }

    public static function isFirstLogin($id)
    {
        return !boolval(self::find($id, ['last_login']));
    }

    public static function getIdFromEmail($email)
    {
        return self::where('email', $email)->get('id');
    }

    private function _updateUserPassword($email, $password)
    {
        $decodeEmail = base64_decode($email);
        $current = $this->_getCurrentUserRecordByEmail($decodeEmail);
        $this->_saveHistoricalPasswordChangeRecord($current);
        $decodePassword = base64_decode($password);
        $this->_removeForcedResetRecord($current['user_id']);
        return self::updateCurrentUser(
            $current['user_id'],
            $decodeEmail,
            $decodePassword
        );
    }

    private function _removeForcedResetRecord($user_id)
    {
        $recordId = ForcePasswordReset::where('user_id', $user_id)
            ->latest('id')
            ->limit(1)
            ->get('id');
        return $recordId ? ForcePasswordReset::destroy($recordId) : null;
    }

    private function _saveHistoricalPasswordChangeRecord($currentRecord)
    {
        return self::saveUserHistory($currentRecord);
    }

    private function _getCurrentUserRecordByEmail($email)
    {
        return self::where('email', $email)
            ->where('status', 1)
            ->get('id as user_id', 'email', 'password');
    }

    public static function updateCurrentUser($id, $email, $password)
    {
        $password = Create::hashPassword($password);
        return self::p_update(compact('id', 'email', 'password'));
    }

    public static function saveUserHistory($currentUserInfo)
    {
        return UserHistory::p_create($currentUserInfo);
    }

    public function logNewLogin()
    {
        $this->last_login = time();
        $this->save();
    }
}
