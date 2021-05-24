<?php

namespace Proaction\Resource\Email;

use Proaction\Domain\Clients\Model\Client;
use Proaction\Domain\Clients\Model\EmailPiping;
use Proaction\Domain\Employees\Model\Employee;
use Proaction\System\Resource\Email\Email;

/**
 * Class to handle all formatting and email redirection.
 *
 * [NOTE: This could be handled as a set of functions within the pipe.php page, however
 * Since we need to access the database, both from the Meta side and the Client side
 * we would need access to the framework Resources & Controllers, so it made more sense
 * To encapsulate the logic in this class]
 *
 */
class Piping
{

    private $_toMatch = '/^To: (.*)/';
    private $_fromMatch = '/^"?From (.*)/';
    private $_subjectMatch = '/^Subject: (.*)/';
    private $_default = 'admin';
    private $_cache = [];

    public function __construct($array)
    {
        $this->lines = $array;

        $this->_registerMetaConnection();
    }

    /**
     * Public method for containing all work done by the class
     *
     * @return Piping
     */
    public function format()
    {
        // get the addressee value, then parse the addressee value to prefix and module
        $this->_addressee();
        $this->_getAddressee();

        // parse the rest of the values
        $this->_from();
        $this->_subject();

        // message needs to some cleanup, and will likely need more after some testing
        $this->_message();
        $this->_cleanUpMessage();
        $this->_prependMessageHeading();

        // using the clientPdo and clientDb handlers, get the _to value
        $this->_to();

        // set the headers();
        $this->_headers();
        // $this->lines = []; // ! Debug: when outputting all lines is not needed, comment this line

        // return this so we can chain the send method
        return $this;
    }

    /**
     * Method to send parsed values from input;
     */
    public function send()
    {
        // phpCore try catch block
        try {
            // code block ....
            $response = Email::to($this->_to)
                ->subject($this->_subject)
                ->message($this->_message)
                ->compose();

            if (!$response) {
                throw new \Exception('Failure to route email properly');
            }
            //$this->message('');
        } catch (\Exception $e) {
        }
    }

    /**
     * Process method to create the _addressee value
     *
     * @return void
     */
    private function _addressee()
    {
        $re = $this->_toMatch;
        $to = current(array_filter($this->lines, function ($v, $k) use ($re) {
            return preg_match($re, $v, $matches);
        }, ARRAY_FILTER_USE_BOTH));

        $this->_addressee = str_replace('To: ', '', $to);
    }

    /**
     * Process method to create the _from value
     *
     * @return void
     */
    private function _from()
    {
        $re = $this->_fromMatch;
        $from = current(array_filter($this->lines, function ($v, $k) use ($re) {
            return preg_match($re, $v, $matches);
        }, ARRAY_FILTER_USE_BOTH));

        $from = str_replace('"From ', '', $from);
        $from = str_replace('From ', '', $from);
        $this->_cache[] = "from: $from";
        $fromParts = explode(' ', $from);
        $this->_cache[] = $fromParts;
        $fromEmail = array_shift($fromParts);
        $this->_cache[] = "fromEmail: $fromEmail";
        $timestamp = implode(' ', $fromParts);
        $this->_cache[] = "timestamp: $timestamp";
        $this->_date = $timestamp;
        $this->_from = $fromEmail;
    }

    /**
     * Process method to create the _subject value
     *
     * @return void
     */
    private function _subject()
    {
        $re = $this->_subjectMatch;
        $subject = current(array_filter($this->lines, function ($v, $k) use ($re) {
            return preg_match($re, $v, $matches);
        }, ARRAY_FILTER_USE_BOTH));

        $subject = str_replace('Subject: ', '', $subject);

        $this->_subject = $subject;
    }

    /**
     * Process method to create the _to value
     *
     * @return void
     */
    private function _getAddressee()
    {
        // phpCore try catch block
        try {
            // code block ....
            $addressee = explode('.', explode('@', $this->_addressee)[0]);

            $this->_prefix = $addressee[0];
            $this->_module = $addressee[1];

            // short-curcuit if no address is found, "unpossible" scenerio, but just a first check
            if (is_null($this->_prefix)) {
                throw new \Exception('No prefix found.');
            }

            // get client data and short-circuit if null
            $this->_clientData = $this->_getClientByPrefix($this->_prefix);

            if (is_null($this->_clientData)) {
                throw new \Exception('Prefix not found.');
            }

            $this->_registerClientConnection();

            return true;
        } catch (\Exception $e) {
            // TODO - LOG the error
            // TODO - BYPASS THE PIPE and send to domain root email admin
            // print_r($this);
            // die($e->getMessage());
        }
    }

    /**
     * Process method to get the _message value
     *
     * @return void
     */
    private function _message()
    {
        // set init state and message values
        $is_header = true;
        $message = '';
        $len = count($this->lines);
        $lines = $this->lines;

        for ($i = 0; $i < $len; $i++) {

            // If we're not in the header, we're in the message until we break out
            if (!$is_header) {
                $message .= $lines[$i] . '<br />';
            }

            // hit an empty line and now we're in the message content
            if (trim($lines[$i]) == "" && $is_header == true) {
                $is_header = false;
            }
        }

        $this->_message = $message;
    }

    /**
     * Method to clean up the message value. Gmail adds some content artifacts that need to be worked around.
     * Need to test other email systems. Nothing will be 100%, but if we can get the bulk of them tested and cleaned up...
     *
     * @return void
     */
    private function _cleanUpMessage()
    {
        $msgClone = $this->_message;

        $msgBreakLines = explode('<br />', $msgClone);

        $len = count($msgBreakLines);

        $contentFlagsFound = 0;

        $newMessage = [];

        if (preg_match('/^--[a-zA-Z0-9]*$/', current($msgBreakLines), $matches)) {

            $key = $matches[0];

            for ($ii = 2; $ii < $len; $ii++) {

                $curLine = $msgBreakLines[$ii];

                if ($curLine != $key) {
                    $newMessage[] = $curLine;
                } else {
                    break;
                }
            }

            $newMessage = array_filter($newMessage);

            $this->_message = implode('<br />', $newMessage);
        }
    }

    /**
     * Add a heading to the message value
     *
     * @return void
     */
    private function _prependMessageHeading()
    {
        $message = $this->_message;

        $this->_message = '<h3>Message sent by: ' . $this->_from . '</h3>';
        $this->_message .= '<p>Time sent: ' . $this->_date . '</p>';
        $this->_message .= "Message: <br /> $message";
    }

    /**
     * Query the meta database and ensure that the prefix exists in the system, otherwise, Log/die;
     *
     * @return mixed    Returns bool status on fail, or the data on success
     */
    private function _getClientByPrefix($prefix)
    {
        return Client::where('client_system_prefix', $prefix)->get();
    }

    /**
     * Create the Client-specific pdo
     *
     * @return object PDO
     */
    private function _registerClientConnection()
    {
        $connectionManager = new Manager();
        $connectionManager->forceConnect($this->_clientData['client_system_prefix']);
    }

    /**
     * Return employee id associated with the client email piping value
     *
     * @return mixed     - on success, return int value of employee_id, otherwise return self to run the method again w/ new _module value;
     */
    private function _getEmployeeIdViaModule()
    {

        // phpCore try catch block
        try {
            // code block ....
            // set module name to this_module
            $module_name = $this->_module ?? $this->_default;

            $result = EmailPiping::where('module_name', $module_name)
                ->get('employee_id');

            // if $r returns null, throw Exception
            if ($result == null) {
                throw new \Exception('null value');
            }

            return $result;
        } catch (\Exception $e) {

            // on Exception set module to null and re-run the method to activate the ternary above, setting module_name to $this->_default
            // admin is set by default in the database (or will be, so this will always return a value)
            $this->_module = null;
            return $this->_getEmployeeIdViaModule();
        }
    }

    /**
     * set the _to value
     *
     * @return void
     */
    private function _to()
    {
        // set value to id
        $id = $this->_getEmployeeIdViaModule();

        $emp = Employee::where('id', $id)->first();
        // set _to to the email from the query
        $this->_to = $emp->email;
    }

    /**
     * Set the _headers values for the module piping
     *
     * @return void
     */
    private function _headers()
    {
        $headers = "From: Proaction <email-routing@zerodock.com> \r\n";
        $headers .= "Reply-To: <email-routing@zerodock.com> \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        $this->_headers = $headers;
    }

    private function _registerMetaConnection()
    {
        new MetaConn();
    }
}
