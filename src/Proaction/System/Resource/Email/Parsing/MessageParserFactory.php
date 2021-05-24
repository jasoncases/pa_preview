<?php
 
namespace Proaction\Resource\Email\Parsing;

class MessageParserFactory
{

    private $appleMailRegex = '/-Apple-Mail-/';
    private $gmailRegex = '/^--[0-9a-zA-Z]+/';
    private $iphoneMailRegex = '/X-Mailer: iPhone Mail/';

    public static function create($messageArray)
    {
        return (new static)->_createMessageParser($messageArray);
    }

    private function _getSender($messageArray)
    {
        if ($this->_messageIsAppleMail($messageArray)) {
            return 'applemail';
        } else if ($this->_messageIsGmail($messageArray)) {
            return 'gmail';
        } else if ($this->_messageIsIphoneMail($messageArray)) {
            return 'iphonemail';
        } else {
            return 'generic';
        }
    }

    private function _messageIsGmail($messageArray)
    {
        foreach ($messageArray as $line) {
            if (preg_match($this->gmailRegex, trim($line))) {
                return true;
            } else {
            }
        }

        return false;
    }

    private function _messageIsAppleMail($messageArray)
    {
        $messageAsString = implode('XXX', $messageArray);
        return preg_match($this->appleMailRegex, $messageAsString);
    }

    private function _createMessageParser($messageArray)
    {
        $parser = $this->_buildParser($messageArray);
        return $parser->run();
    }

    private function _messageIsIphoneMail($messageArray)
    {
        $messageAsString = implode(PHP_EOL, $messageArray);
        return preg_match($this->iphoneMailRegex, $messageAsString);
    }

    private function _buildParser($messageArray)
    {
        switch ($this->_getSender($messageArray)) {
            case 'applemail':
                return new MessageParserAppleMail($messageArray);
            case 'gmail':
                return new MessageParserGmail($messageArray);
            case 'iphonemail':
                return new MessageParserIphoneMail($messageArray);
            default:
                return new MessageParserGeneric($messageArray);
        }
    }
}
