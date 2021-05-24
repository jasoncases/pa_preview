<?php

namespace Proaction\Resource\Email\Routing;

use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\Domain\Tickets\Model\Ticket;
use Proaction\Domain\Tickets\Model\TicketCategory;
use Proaction\Domain\Tickets\Model\TicketCategoryEmployee;
use Proaction\Domain\Tickets\Model\TicketSubscriber;
use Proaction\Domain\Tickets\Model\TicketUser;
use Proaction\System\Resource\Helpers\InputSanitizer;
use Proaction\System\Resource\Token\Token;

class SupportTicketRouter extends SubRouter
{

    private $prefixRe = '/\-(.*?)\@/';

    /**
     * props => [id, category]
     */
    private $category;
    /**
     * props => [id, employee_id, name, email, displayName]
     */
    private $author;
    /**
     * Array of employee_ids
     */
    private $subs;
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
        $this->_getCategory();
        $this->_getTicketAuthor();
        $this->_getCategorySubscribers();
        $ticket_id = $this->_createNewTicket('Support ticket created via email');
        $this->_subscribeAuthorToTicket($ticket_id, $this->author['id']);
        $this->_subscribeDefaultTicketSubs($ticket_id);
    }

    private function _getCategorySubscribers()
    {
        $this->subs = TicketCategoryEmployee::where('category_id', $this->category['id'])
                ->get('employee_id');
    }

    private function _getCategory()
    {
        $this->category = TicketCategory::getDefaultRecord();
    }

    private function _subscribeDefaultTicketSubs($ticket_id)
    {
        foreach ($this->subs as $subscriber_id) {
            TicketSubscriber::p_create(compact('subscriber_id', 'ticket_id'));
        }
    }

    private function _subscribeAuthorToTicket($ticket_id, $subscriber_id)
    {
        $foundTicketUser = TicketUser::where('id', $subscriber_id)->get('employee_id');
        if (boolval($foundTicketUser)) {
            TicketSubscriber::p_create(compact('ticket_id', 'subscriber_id'));
        }
    }

    protected function _createNewTicket($reason)
    {
        $category_id = TicketCategory::getDefaultId();
        $author = $this->author['id'];
        $title = $this->emailParser->subject;
        $description = "<<<<< SUPPORT TICKET GENERATED VIA EMAIL >>>>>";
        $description .= "\r\n\r\n" . InputSanitizer::clean($this->emailParser->message);
        $hash = Token::create();
        return Ticket::p_create(compact('title', 'description', 'author',  'category_id', 'hash'));
    }

    private function _getTicketAuthor()
    {
        $this->author = $this->_checkForExistingUser();
    }

    private function _checkForExistingUser()
    {
        $emp = $this->_userIsEmployee();
        if (!$emp) {
            $user = $this->_userExists();
            if ($user) {
                return $user;
            } else {
                die();
            }
        } else {
            return $emp;
        }
    }

    private function _userIsEmployee() {
        return EmployeeView::where('email', $this->emailParser->from)->get();
    }

    private function _createUser()
    {
        $from = $this->emailParser->from;
        [$name, $email, $displayName] = [$from, $from, $from];
        return TicketUser::p_create(
            compact('name', 'email', 'displayname')
        );
    }

    /**
     * set client prefix and subsequently establish client connection to
     * database
     *
     * @param string $to
     * @return void
     */
    protected function _parseTo($to)
    {
        preg_match($this->prefixRe, $to, $match);
        [$gc, $prefix] = $match;
        $this->client = $this->_getClientByPrefix($prefix);
    }

    private function _userExists()
    {
        return TicketUser::where('email', $this->emailParser->from)
            ->limit(1)
            ->get();
    }

}
