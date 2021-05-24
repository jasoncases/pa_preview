<?php

namespace Proaction\Resource\Email\Parsing;

class MessageParserGeneric extends MessageParser
{
    protected $name = 'Generic';

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

        return $container;
    }

    protected function _extendRegex()
    {
        return [];
    }
}
