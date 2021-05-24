<?php

namespace Proaction\Resource\Email\Parsing;

use Proaction\System\Helpers\Arr;

class MessageParserGmail extends MessageParser
{
    protected $name = 'Gmail';

    private $re_gmail = '/^--[0-9a-zA-Z]+/';
    private $contentHash;
    private $state = 'scan';

    private function _contentHash($line)
    {
        if (preg_match($this->re_gmail, trim($line), $m)) {
            $this->contentHash = $m[0];
            return true;
        }
        return false;
    }

    protected function _parseMessage()
    {

        $container = [];
        $capture = false;

        foreach ($this->messageArray as $k => $line) {

            if ($this->state == 'prime') {
                if (trim($line) == '') {
                    $this->state = 'cache';
                }
            }

            if ($this->_contentHash($line)) {
                if ($this->state == 'scan') {
                    $this->state = 'prime';
                } else if ($this->state == 'cache') {
                    break;
                }
            };

            if ($this->state == 'cache') {
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
