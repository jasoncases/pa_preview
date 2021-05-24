<?php

namespace Proaction\System\Resource\Daemon\ShellDaemon;

use Proaction\Domain\Voice\Model\VoiceAnnouncement;
use Proaction\Domain\Voice\Model\VoiceRecurring;
use Proaction\Domain\Voice\Model\VoiceScheduled;
use Proaction\System\Database\CDB;

class ShellVoice
{
    public function __construct()
    {
        $this->_init();
    }

    private function _init()
    {
        $this->_getUserInsertedTimeVoiceAnnouncements();
        $this->_getSystemScheduledVoiceAnnouncements();
    }


    private function _getUserInsertedTimeVoiceAnnouncements()
    {
        $range = [time(), time() + 60];
        $timed = VoiceScheduled::whereBetween('timed_release', $range)
            ->orderBy('timed_release')
            ->get('*', CDB::raw('UNIX_TIMESTAMP() as hydrated'));

        foreach ($timed as $announce) {
            $this->_storeDefaultFemaleVoiceMessage($announce->text);
        }
    }

    private function _getSystemScheduledVoiceAnnouncements()
    {
        $this->_playDailyScheduledAnnouncements();
        $this->playWeeklyScheduleAnnouncements();
    }

    private function _playDailyScheduledAnnouncements()
    {
        $daily = VoiceRecurring::where('frequency', 'daily')
            ->where('scheduled_release', date('H:i'))
            ->get('*', CDB::raw('UNIX_TIMESTAMP(NOW()) as hydrated'));
        foreach ($daily as $announce) {
            $this->_storeDefaultMaleVoiceMessage($announce->text);
        }
    }

    private function playWeeklyScheduleAnnouncements()
    {
        $weekly = VoiceRecurring::where('dotw', date('w'))
            ->where('frequency', 'weekly')
            ->where('scheduled_release', date('H:i'))
            ->get('*', CDB::raw('UNIX_TIMESTAMP() as hydrated'));
        foreach ($weekly as $announce) {
            $this->_storeDefaultMaleVoiceMessage($announce->text);
        }
    }

    private function _storeVoice($text, $voice_id)
    {
        return VoiceAnnouncement::p_create(compact('text', 'voice_id'));
    }

    private function _storeDefaultFemaleVoiceMessage($text)
    {
        $voice_id = 1; // Joanna, default
        return $this->_storeVoice($text, $voice_id);
    }
    private function _storeDefaultMaleVoiceMessage($text)
    {
        $voice_id = 2; // Joey, default
        return $this->_storeVoice($text, $voice_id);
    }
}
