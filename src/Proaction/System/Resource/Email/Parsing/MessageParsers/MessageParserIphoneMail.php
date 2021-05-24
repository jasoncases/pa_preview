<?php

namespace Proaction\Resource\Email\Parsing;

use Proaction\Domain\Users\Model\ProactionUser;

class MessageParserIphoneMail extends MessageParser
{
    protected $name = 'Iphone';

    protected function _parseMessage()
    {
        $container = [];
        $capture = true;

        foreach ($this->messageArray as $k => $line) {
            $line = trim($line);
            if ($this->_checkAllEndingRegex($line)) {
                break;
            }
            if ($capture) {
                $container[] = $line;
            }
        }
        mail(ProactionUser::defaultAdminEmail(), 'IPHONE container after parsing', print_r($container, true));
        return $container;
    }

    protected function _extendRegex()
    {
        return [];
    }
}
