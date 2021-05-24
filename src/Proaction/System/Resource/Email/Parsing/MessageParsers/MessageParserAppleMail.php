<?php

namespace Proaction\Resource\Email\Parsing;

use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\System\Helpers\Arr;

class MessageParserAppleMail extends MessageParser
{
    protected $name = 'AppleMail';
    private $iphoneRegex = '/Sent from/';
    private $endRegex = '/(--Apple-Mail-[a-zA-Z0-9\-]+)/';

    private $state = 'scan'; // scan, pending, cache;


    protected function _parseMessage()
    {
        /**
         * Need to account for leading content hash present or missing
         * 
         */

        mail(ProactionUser::defaultAdminEmail(), 'APPLE BEFORE after parsing', print_r($this->messageArray, true));

        if (isset($_GET['debug'])) {
            Arr::pre($this->messageArray);
            echo "<hr />";
        }
        $container = [];
        $capture = false;
        $counter = 0;

        foreach ($this->messageArray as $k => $line) {
            $line = trim($line);

            // if we see the content hash, break, if it's not the first
            // line of content
            if (preg_match($this->endRegex, $line)) {
                if ($counter >= 1) {
                    break;
                } else {
                    $this->state = 'pending';
                }
            }

            if ($this->state == 'cache') {
                $container[] = $line;
            }

            if ($line === '' && $this->state == 'pending') {
                $this->state = 'cache';
            };
            $counter++;
        }

        if (isset($_GET['debug'])) {
            Arr::pre($container);
            echo "<hr />";
        }
        mail(ProactionUser::defaultAdminEmail(), 'APPLE container after parsing', print_r($container, true));
        return $container;
    }

    private function _hasLeadingContentHeader($line)
    {
        return preg_match($this->endRegex, $line);
    }

    protected function _extendRegex()
    {
        return [$this->iphoneRegex];
    }
}
