<?php

namespace Proaction\System\Resource\Comms;

/**
 * Comms layer will take a type of communication, a message key phrase
 * and an optional array of data.
 *
 * Comms will choose the mode of communication, ie email => EmailLayer,
 * then EmailLayer will choose the message and any additional options
 */
class CommsReceiver
{

    private $commTypes = [
        'email' => '\Proaction\System\Comms\CommEmail',
        'sms' => '\Proaction\System\Comms\CommSMS',
        'flash' => '\Proaction\System\Comms\CommFlash',
        'voice' => '\Proaction\System\Comms\CommVoice'
    ];

    /**
     *
     *
     * @param string $type          - email, sms, voice, alert
     * @param string $typeMsgKey    - (.) separated type.message-name
     *                                i.e, 'tasks.open'
     * @param array $options        - array of params, MUST have rec id
     *                                properly named, i.e. - task_id
     * @return void
     */
    public static function send($type, $typeMsgKey, $options = []){
        return (new static)->_mode($type)->send($typeMsgKey, $options);
    }

    private function _mode($type){
        $type = $this->commTypes[$type];
        return new $type();
    }

}
