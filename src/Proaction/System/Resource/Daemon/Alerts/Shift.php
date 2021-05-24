<?php

namespace Proaction\System\Resource\Daemon;

use Proaction\Model\Employee;
use Proaction\System\Helpers\Arr;

/**
 * ShiftAlert handles all alerts for employee shifts, tardy/wrapup/etc.
 */
class ShiftAlert extends AlertType
{
    protected $type = 'shift';

    protected function shift_tardy_warning()
    {
        if (!Employee::isClockedIn($this->employeeId)) {
            return $this->_getVoiceAlert('shift_tardy_warning')->send();
        }
    }

    protected function shift_tardy_notice()
    {
        if (!Employee::isClockedIn($this->employeeId)) {
            $this->_getVoiceAlert('shift_tardy_warning')->send();
            return $this->_getEmailAlert('shift_tardy_notice')->send();
        }
    }

    protected function shift_wrapup()
    {
        // Arr::pre($this->_getVoiceAlert('shift_wrapup'));
        // die();
        return $this->_getVoiceAlert('shift_wrapup')->send(3600);
    }
}
