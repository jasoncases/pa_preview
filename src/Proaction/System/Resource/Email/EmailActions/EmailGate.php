<?php

namespace Proaction\System\Resource\Email\EmailActions;

class EmailGate
{

    protected $messages = [
        'tardySelfAlert' => '\Proaction\System\Resource\Email\EmailActions\Tardy',
        'empCallout' => '\Proaction\System\Resource\Email\EmailActions\EmpCallout',
        'shiftTardyNotice' => '\Proaction\System\Resource\Email\EmailActions\ShiftTardyNotice',
        'forcedClockout' => '\Proaction\System\Resource\Email\EmailActions\ForcedClockout',
        'alertLunchNotice' => '\Proaction\System\Resource\Email\EmailActions\AlertLunchNotice',
        'alertBreakNotice' => '\Proaction\System\Resource\Email\EmailActions\AlertBreakNotice',
        'remoteTimeclock' => '\Proaction\System\Resource\Email\EmailActions\RemoteTimesheetAction',
    ];

    public static function send($type, $options = [])
    {
        return (new static)->_send($type, $options);
    }

    private function _send($type, $options)
    {
        $sendMail = $this->_createEmailAction($type, $options);
        if (isset($options['test'])) {
            return $sendMail->test();
        } else {
            return $sendMail->send();
        }
    }

    private function _createEmailAction($type, $options)
    {
        $clsName = $this->messages[$type];
        if (!class_exists($clsName)) {
            throw new \Exception("Missing classname, $clsName. EmailGate: " . __CLASS__);
        }
        return new $clsName($options);
    }
}
