<?php

namespace Proaction\System\Resource\Daemon;

/**
 * BreakAlert handles alerts having to do with an employee going over
 * their alloted time on their paid breaks
 */
class BreakAlert extends AlertType
{
    protected $type = 'break';

    protected function warning()
    {
        return $this->_getVoiceAlert('alert_break_warning')->send();
    }
    protected function over()
    {
        return $this->_getVoiceAlert('alert_break_over')->send();
    }
    protected function notice()
    {
        $this->_getVoiceAlert('alert_break_notice')->send();
        return $this->_getEmailAlert('alert_break_notice')->send();
    }
}
