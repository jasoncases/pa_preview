<?php

namespace Proaction\Resource\Email\Routing;

use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\Resource\Email\Piping;

class RouteFactory
{
    /**
     * ...Injection regex for pushing a record into the database
     *
     * @var string
     */
    private $ticketInjection = '/^support-/';

    // task/tasks and code/codes are both used as it would be a common
    // misspelling, this allows clients to us either
    private $taskInjection = '/^task-/';
    private $tasksInjection = '/^tasks-/';
    private $codeInjection = '/^code-/';
    private $codesInjection = '/^codes-/';

    private $archiveTicketReply = '/-ticket-/';
    private $ticketReply = '/-support-/';
    private $taskReply = '/-task-/';
    private $codeReply = '/-code-/';


    private $rawEmail;

    public static function create($address, $email)
    {
        return (new static)->_create($address, $email);
    }

    private function _create($address, $email)
    {
        $this->rawEmail = $email;
        return $this->_build(trim($address));
    }

    private function _build($address)
    {
        switch ($address) {
                // >>> Support ticket injection
            case (preg_match($this->ticketInjection, $address) ? true : false):
                return $this->_createTicketInjectionRoute();
                // >>> Task Manager injection, singular 'task-{prefix}'...
            case (preg_match($this->taskInjection, $address) ? true : false):
                return $this->_createTaskInjectionRoute();
                // >>> ... and plural 'tasks-{prefix}'
            case (preg_match($this->tasksInjection, $address) ? true : false):
                return $this->_createTaskInjectionRoute();
                // >>> Code Tracker injection, singular 'code-{prefix}'...
            case (preg_match($this->codeInjection, $address) ? true : false):
                return $this->_createCodeInjectionRoute();
                // >>> ... and plural 'codes-{prefix}'
            case (preg_match($this->codesInjection, $address) ? true : false):
                return $this->_createCodeInjectionRoute();
                // >>> Ticketing module reply routing
            case (preg_match($this->ticketReply, $address) ? true : false):
                return $this->_createTicketReplyRoute();
            case (preg_match($this->taskReply, $address) ? true : false):
                return $this->_createTaskReplyRoute();
            case (preg_match($this->codeReply, $address) ? true : false):
                return $this->_createCodeReplyRoute();
                // >>> Default email routing
            case (preg_match($this->root, $address) ? true : false):
                return $this->_createDefaultEmailRoute($address);
            default:
                return $this->_createDefaultEmailRoute($address);
        }
    }

    private function _emailParser()
    {
        return new \Proaction\Resource\Email\Parser($this->rawEmail);
    }

    private function _createTicketInjectionRoute()
    {
        return new SupportTicketRouter($this->_emailParser());
    }

    private function _createTaskInjectionRoute()
    {
        return new TaskInjection($this->_emailParser());
    }

    private function _createCodeInjectionRoute()
    {
        $from = (string) $this->_emailParser()->from;
        mail(ProactionUser::defaultAdminEmail(), "Code Email Injection attempted by $from", print_r($this->rawEmail, true));
        mail($from, 'Proaction Code Tracker email inject not currently operational', '');
        return;
    }

    private function _createTicketReplyRoute()
    {
        return new TicketReply($this->_emailParser());
    }

    private function _createTaskReplyRoute()
    {
        return;
    }

    private function _createCodeReplyRoute()
    {
        return;
    }

    private function _createDefaultEmailRoute($address = '')
    {
        $pipe = new Piping($this->rawEmail);
        mail(ProactionUser::defaultAdminEmail(), 'Default Email Route' . $address,  print_r($this->rawEmail, true));
        // return $pipe->format()->send();
    }
}
