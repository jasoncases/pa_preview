<?php

namespace Proaction\Resource\Email\Parsing;

use Proaction\Domain\Users\Model\ProactionUser;


/**
 * Message parser allows us to capture and clean up incoming email. 
 * This is specifically related to the ticket module, but this could be
 * extended to be used in other areas of the site
 */
class MessageParser
{
    protected $name;
    protected $messageArray;
    // do not work with forwarding. 
    protected $onWroteDayRegex = '/On (Mon|Tue|Wed|Thu|Fri|Sat|Sun)/';
    protected $onWroteMonthRegex = '/On (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)/';

    protected $fwdRe = '/Begin forwarded message/';

    public function __construct($messageArray)
    {
        $this->messageArray = $this->_removeLeadingEmtpyRows($messageArray);
        // capture the raw email data while debugging
        mail(ProactionUser::defaultAdminEmail(), 'MessageParser - Raw email capture', print_r($this, true));
    }

    /**
     * Secondary formatting after the message gets parsed. Different
     * email clients have different rules and formatting for their email
     * so we have different parsing classes to handle each. They are 
     * recognized by unique strings located in the meta data, and any
     * array that slips by is captured by the generic parser so info 
     * gets returned, even if it's not cleanly formatted
     *
     * @return void
     */
    public function run()
    {
        if (isset($_GET['debug'])) {
            echo "<h2>" . $this->name . "</h2>";
        }

        $stagingMessage = $this->_finalFormatting(
            $this->_parseMessage()
        );
        return $stagingMessage;
    }

    /**
     * Final round of formatting called in run. Trimming rows before 
     * returning the final formatted string. MessageFormatter handles
     * minor replacements like `=E2=80=99` to single quote chars as well
     * as recombining the array into a string. Imploding ended up with 
     * inconsistent results so we go line by line, reassemble and insert
     * some unique strings that then get replaced with <br/> tags
     *
     * @param array $messageArray
     * @return string
     */
    private function _finalFormatting($messageArray)
    {
        $messageArray = $this->_removeLeadingEmtpyRows($messageArray);
        $messageArray = $this->_removeTrailingEmptyRows($messageArray);
        $messageArray = $this->_chunkForwardedMessages($messageArray);
        return $this->_messageFormatter($messageArray);
    }

    private function _chunkForwardedMessages($messageArray)
    {
        return $messageArray;
    }

    private function _messageFormatter($messageArray)
    {
        return MessageFormatter::format($messageArray);
    }

    /**
     * 
     *
     * @param array $message
     * @return array
     */
    private function _removeTrailingEmptyRows($message)
    {
        while (end($message) === '') {
            array_pop($message);
        }
        return $message;
    }

    /**
     * Method holder. Nothing happens here, this method in the child
     * classes is where all the parsing and formatting happens to clean
     * the incoming message
     *
     * @return void
     */
    protected function _parseMessage()
    {
    }

    /**
     * 
     *
     * @param array $messageArray
     * @return array
     */
    private function _removeLeadingEmtpyRows($messageArray)
    {
        while (current($messageArray) === '') {
            array_shift($messageArray);
        }
        return $messageArray;
    }

    /**
     * There are a couple of strings that appear in different email 
     * clients that can signify the message is over. We set this up so
     * the child classes could extend them as well. 
     *
     * @param string $line
     * @return bool
     */
    protected function _checkAllEndingRegex($line)
    {
        $regex = [$this->onWroteDayRegex, $this->onWroteMonthRegex];
        $regex = array_merge($regex, $this->_extendRegex());
        foreach ($regex as $re) {
            if (preg_match($re, $line, $M)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Allows the child classes to extend the possible `ending regex` 
     * values if a particular parser needs 
     *
     * @return array
     */
    protected function _extendRegex()
    {
        return [];
    }
}
