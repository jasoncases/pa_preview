<?php

namespace Proaction\System\Resource\Email\EmailActions;

use Proaction\Domain\Clients\Model\ClientInfo;
use Proaction\Domain\Clients\Resource\ProactionClient;
use Proaction\Domain\Users\Resource\UserFactory;
use Proaction\System\Resource\Email\Email;
use Proaction\System\Resource\Helpers\Arr;

class EmailAction
{
    protected $client;
    protected $author;
    protected $from;
    protected $root = '@zerodock.com';
    protected $templateName = '';
    protected $test = false;
    protected $addAdmin = true;
    protected $includeAuthor = true;
    protected $moduleName = '';
    protected $addAssigneeToEmail;
    protected $removeSubscribers;
    protected $subscribers;

    /**
     * Container method that finalizes the creation of message and
     * subject before calling the final compose method. Allows the dev
     * to do any special handling needed for the completion of the
     * action.
     *
     * @return void
     */
    public function send()
    {
    }

    protected function _extendInit()
    {
    }

    protected function _getData($options = [])
    {
    }

    /**
     * Create from sender from client prefix and task hash. The hash
     * is an identifier for the email parsing when a user replies to a
     * generated task email.
     *
     * @param Task $task
     * @return string
     */
    protected function _formatFrom()
    {
    }

    /**
     * Format the subject line-by-line and return it all as a string
     *
     * @param Task $task
     * @param boolean $closed
     * @return string
     */
    protected function _formatSubject()
    {
    }

    /**
     * Generic get message from EmailTemplate and parse via the Temlpat-
     * er class with the provided data. Data is automatically merged w/
     * the author and client arrays.
     *
     * author array: [email, displayName, authorId]
     *
     * @param array $model
     * @return string
     */
    protected function _getMessage($model)
    {
        $message = view('System.Email.' . $this->templateName, $model)->render();
        return $message;
    }


    /**
     * A container method for sanitizing the subject string. Currently
     * only holds entity_decode, but could extend to use other methods,
     * so it was broken out, preemptively.
     *
     * @param string $subject
     * @return string
     */
    protected function _sanitizeSubject($subject)
    {
        $subject = html_entity_decode($subject);
        return $subject;
    }

    /**
     * init method to set the class values. Only REQUIRED item to be set
     * in the constructor is the `task` prop, as `subscribers` and
     * `from` props extend from it.
     *
     * @return void
     */
    protected function _init()
    {
        $this->_setAuthor();
        $this->_setClient();
        $this->_setFrom();
        $this->_extendInit();
    }

    /**
     * Create a default admin email if no subscribers are found.
     *
     * client_prefix-admin@`root`.com
     *
     * @return array
     */
    private function _createDefaultAdminEmail()
    {
        return [$this->client['client_prefix'] . '-' . 'admin' . $this->root];
    }

    /**
     * Set the `from:` field of the outgoing email.
     *
     * @return string
     */
    protected function _setFrom()
    {
        $this->from = 'no-reply@zerodock.com';
    }

    /**
     * Create author prop by currently logged in User()
     *
     * @return void
     */
    private function _setAuthor()
    {
        $user = UserFactory::create();
        $this->author = [
            'email' => $user->get('email'),
            'displayName' => $user->get('displayName'),
            'authorId' => $user->get('employeeId'),
        ];
    }

    /**
     * Create client prop by currently active client
     *
     * @return void
     */
    private function _setClient()
    {
        $client = ClientInfo::getByUid(ProactionClient::uid());
        $this->client = [
            'client_prefix' => ProactionClient::prefix(),
            'client_uid' => ProactionClient::uid(),
            'client_name' => $client->name . ' ' . $this->moduleName,
        ];
    }

    /**
     * Create and send the email, if the incoming options have a key
     * named 'test', the email is bypassed and an array is returned w/
     * details of the communication, allows us to output in the admin
     * area
     *
     * @param string $message
     * @param string $subject
     * @return mixed (bool/array[test])
     */
    protected function _compose($message, $subject)
    {
        if ($this->test) {
            return $this->_displayDetails($message, $subject);
        } else {
            return $this->_fireSend($message, $subject);
        }
    }

    /**
     * Return all relavent data for testing
     *
     * @param string $message
     * @param string $subject
     * @return array
     */
    private function _displayDetails($message, $subject)
    {
        return  [
            'message' => $message,
            'subject' => $subject,
            'addAssigneeToEmail' => $this->addAssigneeToEmail ? "Yes" : "No",
            'removeSubscribers' => $this->removeSubscribers ? "Yes" : "No",
            'includeAuthor' => $this->includeAuthor ? "Yes" : "No",
            'subscribers' => print_r($this->subscribers, true),
            'templateName' => $this->templateName,
            'addAdmin' => $this->addAdmin ? "Yes" : "No",
        ];
    }

    private function _fireSend($message, $subject)
    {
        if (!empty($this->subscribers)) {
            return Email
                ::to($this->subscribers)
                ->from($this->from, $this->client['client_name'])
                ->message($message)
                ->subject($subject)
                ->compose();
        }
    }

    public function test()
    {
        $this->test = true;
        return $this->send();
    }
}
