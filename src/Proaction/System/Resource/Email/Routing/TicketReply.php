<?php

namespace Proaction\Resource\Email\Routing;

use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\Domain\Tickets\Model\Ticket;
use Proaction\Domain\Tickets\Model\TicketCategory;
use Proaction\Domain\Tickets\Model\TicketReply as ModelTicketReply;
use Proaction\Domain\Tickets\Model\TicketStatus;
use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\Domain\Tickets\Model\TicketSubscriber;
use Proaction\Domain\Tickets\Model\TicketUser;
use Proaction\Resource\Token;
use Proaction\System\Resource\Comms\Comms;
use Proaction\System\Resource\Email\Email;
use Proaction\System\Resource\Helpers\InputSanitizer;

/**
 *
 */
class TicketReply extends SubRouter
{

    private $ticket;
    protected $client;
    private $replyAuthor;

    /**
     *
     * @param \Proaction\Resource\Email\Parser
     * */
    public function __construct($emailParser)
    {
        parent::__construct($emailParser);
        $this->_init();
    }

    protected function _extendInit()
    {
        $this->_parseTo($this->emailParser->to);
        $this->_reopenClosedTicket();
        $this->_getReplyAuthor();
        $this->_storeReplyComponents();
        $this->_sendReply();
    }

    private function _sendReply()
    {
        Comms::send('email', 'ticket.reply', ['reply_id' => $this->reply->id]);
    }

    /**
     * Inject open status for any closed ticket that receives a reply
     * email
     *
     * @return void
     */
    private function _reopenClosedTicket()
    {
        $closedStatusId = TicketStatus::where('LOWER(status)', 'closed')->get('id');
        // perform the update to change closed ticket to open
        if ($this->ticket->status_id == $closedStatusId) {
            Ticket::p_update([
                'id' => $this->ticket->ticket_id,
                'status_id' => 1,
            ]);
        }
    }

    private function _getReplyAuthor()
    {
        $user = $this->_getTicketUser();
        if (is_null($user)) {
            $user = $this->_createUserFromReplyAuthorEmail($this->emailParser->from);
        }
        $this->replyAuthor = $user;
    }

    private function _getTicketUser()
    {
        return TicketUser::where('email', $this->emailParser->from)->get(
            [
                'id as ticket_user_id',
                'name',
                'email',
                'displayName'
            ]
        );
    }

    private function _createUserFromReplyAuthorEmail($email)
    {
        $employee = $this->_getEmployeeFromEmail($email);
        if (!$employee) {
            // create new
            $userId = $this->_createNewTicketUserFromEmail($email);
            return [
                'ticket_user_id' => $userId,
                'name' => $email,
                'displayName' => $email,
                'email' => $email,
                'type' => 'guest',
            ];
        } else {
            return [
                'ticket_user_id' => $employee->id,
                'name' => $employee->name,
                'displayName' => $employee->displayName,
                'email' => $employee->email,
                'type' => 'employee',
            ];
        }
    }

    private function _getEmployeeFromEmail($email)
    {
        return EmployeeView::where('email', $email)
            ->get(
                [
                    'id as employee_id',
                    'email',
                    'first_name as name',
                    'displayName',
                ]
            );
    }

    private function _createNewTicketUserFromEmail($email)
    {
        return TicketUser::p_create([
            'email' => $email,
            'name' => $email,
            'displayName' => $email,
        ]);
    }

    private function _storeReplyComponents()
    {
        $this->_storeReplyAuthor();
        $this->reply = $this->_storeReply();
        $this->_subscribeReplyAuthor();
    }

    private function _storeReply()
    {
        $author = $this->replyAuthor['ticket_user_id'];
        $ticket_id = $this->ticket['ticket_id'];
        $reply = InputSanitizer::clean($this->emailParser->message);
        $source = 'email_routing';
        return ModelTicketReply::p_create(
            compact('author', 'ticket_id', 'reply', 'source')
        );
    }

    private function _storeReplyAuthor()
    {
        if (!TicketUser::where('email', $this->emailParser->from)->get()) {
            return TicketUser::p_create($this->replyAuthor);
        }
        return true;
    }

    private function _subscribeReplyAuthor()
    {
        $subscriber_id = $this->replyAuthor['ticket_user_id'];
        $ticket_id = $this->ticket['ticket_id'];
        $exists = TicketSubscriber::where('subscriber_id', $subscriber_id)
            ->where('ticket_id', $ticket_id)
            ->first();
        if (!$exists) {
            return TicketSubscriber::p_create(compact('subscriber_id', 'ticket_id'));
        }
        return true;
    }

    /**
     * Undocumented function
     *
     * @param string $to
     * @return void
     */
    protected function _parseTo($to)
    {
        [
            $prefix,
            $ticket,
            $hash,
        ] = explode('-', str_replace($this->domain, '', $to));
        $this->client = $this->_getClientByPrefix($prefix);
        $this->_registerClientConnection();
        $this->ticket = $this->_getTicketByHash($hash);
    }


    private function _getTicketByHash($hash)
    {

        if (!$this->_validateHash($hash)) {
            // create and return a new ticket....
            return $this->_createNewTicket("Hash missing from reply-to email");
        } else {
            return Ticket::where('hash', $hash)
                ->get(['author', 'id as ticket_id', 'status_id']);
        }
    }

    private function _validateHash($hash)
    {
        if (gettype($hash) != 'string') {
            return false;
        }
        if (strlen($hash) <= 0) {
            return false;
        }
        if (!preg_match('/^[a-zA-Z0-9]{32}$/', $hash)) {
            return false;
        }
        return true;
    }

    private function _createNewTicket($reason)
    {
        $category_id = TicketCategory::getDefaultId();
        $title = 'Error processing email reply. New ticket created';
        $author = $this->replyAuthor['ticket_user_id'];
        $description = "TICKET AUTOMATICALLY GENERATED: $reason \r\n\r\n ";
        $description .= "Ticket title: " . InputSanitizer::clean($this->emailParser->subject);
        $hash = Token::create();
        return Ticket::p_create(compact('title', 'description', 'author',  'category_id', 'hash'));
    }

    private function _registerClientConnection()
    {
        Email::to(ProactionUser::defaultAdminEmail(), '\\Domain\\System\\Resource\\Email\\Routing\\TicketReply::_registerClientConnection()', 'Need to create the client connection on email routing.');
    }
}
