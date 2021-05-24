<?php

namespace Proaction\System\Resource\Alerts\VoiceAlert;

use Proaction\Domain\Voice\Model\VoiceAnnouncement;
use Proaction\System\Model\Alerts\AutomatedAlert;
use Proaction\System\Resource\Alerts\Alert;
use Proaction\System\Resource\Templater\Templater;

/**
 * Voice alert takes the incoming template and creates a new voice
 * announcement, via \Proaction\Model\VoiceAnnouncement
 */
class VoiceAlert extends Alert
{

    protected $type = 'voice';

    /**
     * Store a new voice announcement to the database, ultimately
     * returning a boolean status value
     *
     * @return bool
     */
    protected function _send($expiration = 0): bool
    {
        $message = Templater::parse($this->template, $this->data);
        if ($this->_archiveMessage((string) $message, $expiration)) {
            return VoiceAnnouncement::sendNewAnnouncement(
                $message,
                '2',
                'daemon',
            );
        }
        return false;
    }

    /**
     * Returns false if a `fresh` message exists in the database w/ a 
     * matching hash
     *
     * @param string $message
     * @return void
     */
    private function _archiveMessage($message, $expiration = 0)
    {
        return AutomatedAlert::scan($message, $this->employeeId, $expiration);
    }
}
