<?php

namespace Proaction\Resource\Email\Routing;

use Proaction\Model\EmployeeView;
use Proaction\Model\Tasks\Task;
use Proaction\Model\Tasks\TaskCategory;
use Proaction\Model\Tasks\TaskCategoryEmployee;
use Proaction\Resource\Ticket\PAInputSanitzier;
use Proaction\Resource\Token;
use Proaction\System\Helpers\Arr;

class TaskInjection extends SubRouter {

    private $prefixRe = '/\-(.*?)\@/';
    private $category;
    private $subs;
    private $author;
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
        $this->_getCategorySubscribers();
        $this->_getTicketAuthor();
        $task_id = $this->_createNewTask();
    }

    private function _createNewTask() {
        $author = $this->author['id'];
        $title = $this->emailParser->subject;
        $description = "<<<<< TASK GENERATED VIA EMAIL >>>>>";
        $description .= "\r\n\r\n" . PAInputSanitzier::clean($this->emailParser->message);
        $category_id = TaskCategory::getDefaultId();
        $hash = Token::create();
        return Task::p_create(
            compact(
                'title', 'author', 'description', 'category_id', 'hash'
            )
        );
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

    private function _getCategorySubscribers()
    {

        $this->subs = Arr::flatten(
            TaskCategoryEmployee::where('category_id', $this->category['id'])
                ->get('employee_id')
        ) ?? [];
    }

    private function _getCategory()
    {
        $this->category = TaskCategory::getDefaultRecord();
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
        $this->_registerClientConnection();
    }

    private function _userExists()
    {
        return $this->_mockUsers()
            ->where('email', $this->emailParser->from)
            ->limit(1)
            ->get();
    }

    private function _mockUsers()
    {
        return $this->_mockModel('mod_ticket_users');
    }

    private function _createUser()
    {
        $from = $this->emailParser->from;
        [$name, $email, $displayName] = [$from, $from, $from];
        return $this->_mockUsers()->save(
            compact('name', 'email', 'displayname')
        );
    }
}
