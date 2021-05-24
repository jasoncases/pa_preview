<?php

namespace Proaction\System\Resource\Comms;

/**
 * A wrapper that routes to the proper domain email gate
 *
 * TODO - This will eventually do it's work in another thread
 */
class CommEmail
{

    private $emailClasses = [
        'task' => '\Proaction\Domain\Tasks\Resource\EmailActions\TaskEmailGate',
        'ticket' => '\Proaction\Domain\Tickets\Resource\EmailActions\TicketEmailGate',
        'code' => '\Proaction\Domain\Codes\Resource\EmailActions\CodeEmailGate',
        'system' => '\Proaction\System\Resource\Email\EmailActions\EmailGate',
        'timesheet' => '\Proaction\Domain\Timesheets\Resource\EmailActions\TimesheetEmailGate',
    ];

    public function send($message, $options)
    {
        [$domainType, $messageType] = $this->_validate($message);
        $this->options = $options;
        return $this->_generateDomainGate($domainType)->send($messageType, $options);
    }

    private function _generateDomainGate($type)
    {
        $gate = $this->emailClasses[$type];
        if (!class_exists($gate)) {
            throw new \Exception("Requested email communication gate [$type] not found");
        }
        return new $gate();
    }

    /**
     * TODO - INSERT VALIDATION ONCE THE REST OF THE SYSTEM IS BUILT OUT
     *
     * validate and split the incoming message key into domainType, ie.,
     * tasks, tickets, codes, system and the message type, which is the
     * specific message requested, split by a '.' character
     *
     * type gets trim'ed of 's' char from the right side
     * tasks.open           => ['task', 'open']
     * codes.replyAndClose  => ['code', 'replyAndClose']
     *
     * @param string $messageKey
     * @return void
     */
    private function _validate($messageKey)
    {
        $splitKey = explode('.', $messageKey);
        $splitKey[0] = rtrim($splitKey[0], 's');
        return $splitKey;
    }
}
