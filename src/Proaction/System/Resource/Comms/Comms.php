<?php

namespace Proaction\System\Resource\Comms;

/**
 * Comms layer will take a type of communication, a message key phrase
 * and an optional array of data.
 *
 * Comms will choose the mode of communication, ie email => EmailLayer,
 * then EmailLayer will choose the message and any additional options
 */
class Comms
{

    private $commTypes = [
        'email' => 'Proaction\System\Resource\Comms\CommEmail',
        'sms' => 'Proaction\System\Resource\Comms\CommSMS',
        'flash' => 'Proaction\System\Resource\Comms\CommFlash',
        'voice' => 'Proaction\System\Resource\Comms\CommVoice'
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
        // . ' > /dev/null 2>/dev/null &'
        // return shell_exec("./cgi-bin/proaction_comms.sh -c ". Client::prefix() ." -t $type -m $typeMsgKey -o " . escapeshellarg(json_encode($options)) . ' > /dev/null 2>/dev/null &');
        return (new static)->_mode($type)->send($typeMsgKey, $options);
    }

    private function _mode($type){
        $type = $this->commTypes[$type];
        return new $type();
    }

}
