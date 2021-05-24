<?php

namespace Proaction\Domain\Employees\Service;

use Exception\NonUniquePinException;
use Proaction\Domain\Employees\Model\EmployeePermissions;
use Proaction\Domain\Users\Model\Pin;
use Proaction\Domain\Users\Model\UserSetting;

class UpdateOther {
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function commit(){
        $this->_updatePin();
        $this->_updateRemoteAccess();
        $this->_updatePermissions();
    }

    private function _updatePin()
    {
        if (isset($this->data['pin']) && boolval($this->data['pin'])){
            if (!Pin::isUnique($this->data['pin'])){ 
                throw new NonUniquePinException();
            } else {
                Pin::updateEmployeePin($this->data['id'], $this->data['pin']);
            }
        } 
    }

    /**
     * allow_remote_timesheet_action is a value that is set in the 
     * user_settings table. It is boolean and always set, so if we 
     * receive this put data and it's not set, we need to set it to 0
     * and if it is set, we need to set it to true
     *
     * @return void
     */
    private function _updateRemoteAccess() {
        return UserSetting::updateValue(
            'allow_remote_timesheet_action',
            isset($this->data['allow_remote_timesheet_action']), 
            $this->data['id']);
        
    }

    private function _updatePermissions() {
        if (!isset($this->data['permission_id'])) {
            return;
        }
        return EmployeePermissions::updatePermission(
            $this->data['id'],
            $this->data['permission_id'],
        );
    }
}