<?php

namespace Proaction\Resource\Email\Parsing;

class MessageFormatter
{

    protected $messageArr;
    protected $messageString;

    protected $re_URL = '/(http.+?) /';

    public static function format($messageArray)
    {
        return (new static)->_format($messageArray);
    }

    protected function _format($messageArray)
    {
        $this->messageArr = $messageArray;
        $this->_recombineLines();
        return $this->finalMessage;
    }

    /**
     * Loops through the lines and combines lines where needed 
     * Apple mail adds an '=' to the end of the line if there is a break
     * Empty lines are given a special charset that is *UNLIKELY* to be
     * present in a generic email. This character is then replaced by a
     * BR tag to give a line break.
     * 
     * TODO -----------------------------------------------------------
     * TODO | eventually we may want to enclose "lines" in P tags,    |
     * TODO | rather than rely on BR tags to do the formatting work   |
     * TODO -----------------------------------------------------------
     * 
     * @return void
     */
    protected function _recombineLines()
    {
        $len = count($this->messageArr);
        for ($ii = 0; $ii < $len; $ii++) {
            $currLine = &$this->messageArr[$ii];
            if ($currLine === '') {
                $currLine = ':-||-:';
            }
            if (substr($currLine, -1) === '=') {
                $nextLine = &$this->messageArr[$ii + 1];
                $currLine = rtrim($currLine, '=') . $nextLine;
                $nextLine = '';
            }
        }

        $this->finalMessage = $this->_allStringFormatting(
            implode(' <br />', $this->messageArr)
        );
    }

    /**
     * Container method for any and all string formatting that needs
     * to be done on the message string
     *
     * @param string $str
     * @return string
     */
    protected function _allStringFormatting($str)
    {
        $str = $this->_parseUrls($str);
        $str = $this->_searchAndReplace($str);
        return $str;
    }

    /**
     * Add all str_replace commands to this method. 
     *
     * @param string $str
     * @return string
     */
    protected function _searchAndReplace($str)
    {
        $str = str_replace(':-||-:', '<br />', $str);
        $str = str_replace('=20', ' ', $str);
        $str = str_replace('&', 'and', $str);
        $str = str_replace('=E2=80=99', "'", $str);
        $str = str_replace('=E2=80=9C', "'", $str);
        $str = str_replace('=E2=80=9D', "'", $str);
        $str = str_replace('=EF=BB=BF', "", $str);
        return $str;
    }

    /**
     * Captures URLS in the email and turns them into proper anchor tags
     * Additional formatting grabs the website name from the URL and
     * inserts that as the text for the link
     *
     * @param  string $str
     * @return string
     */
    protected function _parseUrls($str)
    {
        // $re = '/^(http:\/\/|https:\/\/)?(.*?\.)?(.*?).com/';
        // preg_match_all($this->re_URL, $str, $matches);
        // $fullMatch = $matches[0]; // just for clarity w/ PHP match handle
        // $patternMatch = $matches[1];
        // foreach ($patternMatch as $match) {
        //     preg_match($re, $match, $m);
        //     $domain = $m[3];
        //     $str = str_replace($match, $this->_renderAnchorTag($match, $domain), $str);
        // }
        return $str;
    }

    /**
     * Simple render anchor tag, returns a string
     *
     * @param  string $url
     * @param  string $domain
     * @return string
     */
    protected function _renderAnchorTag($url, $domain)
    {
        $domain = ucfirst($domain);
        return "<a href=\"$url\" target=\"_blank\">$domain</a>";
    }
}
