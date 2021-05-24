<?php

namespace Proaction\System\Resource\Daemon;

/**
 * LunchAlert handles alerts having to do with an employee going over
 * their alloted time on their unpaid lunch break
 */
class LunchAlert extends AlertType
{
    protected $type = 'lunch';

    protected function warning()
    {
        return $this->_getVoiceAlert('alert_lunch_warning')->send();
    }
    protected function over()
    {
        return $this->_getVoiceAlert('alert_lunch_over')->send();
    }
    protected function notice()
    {
        $this->_getVoiceAlert('alert_lunch_notice')->send();
        $this->_getEmailAlert('alert_lunch_notice')->send();
    }
}
