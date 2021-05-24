<?php

namespace Proaction\System\Resource\Helpers;

use Proaction\System\Resource\Token\Token;

class InputSanitizer
{

    private $text;
    private $tagsToIgnore;

    private $reTagPairs = [
        'img' => '/(<img.*?>)/',
        'a' => '/(<a.*?<\/a>)/',
        'video' => '/(<video.*?><\/video>)/',
    ];

    private $ignoredTagPairs = [];


    public static function clean($text, $tagsToIgnore = [])
    {
        return (new static)->_clean($text, $tagsToIgnore);
    }

    private function _clean($text, $tagsToIgnore)
    {
        $this->_init($text, $tagsToIgnore);
        return $this->_returnCleanedText();
    }

    private function _init($text, $tagsToIgnore)
    {
        $this->text = $text;
        $this->tagsToIgnore = $tagsToIgnore;
    }

    private function _returnCleanedText()
    {
        $text = $this->__processIgnoredTags();
        return $this->__processAllSanitizedInput($text);
    }

    private function __processAllSanitizedInput($text)
    {
        $text = str_replace("″", '"', $text);
        $text = str_replace("‟", '"', $text);
        $text = str_replace("ˮ", '"', $text);
        $text = str_replace("“", '"', $text);
        $text = str_replace("”", '"', $text);
        return $text;
    }

    private function __processIgnoredTags()
    {
        if (empty($this->tagsToIgnore)) {
            $this->text = $this->_validateText($this->text);
            return htmlentities($this->text);
        }
        return $this->_ignoreProvidedTags();
    }

    private function _validateText($str)
    {
        $str = LinkParser::toSql($str);
        return $str;
    }

    private function _ignoreProvidedTags()
    {
        // $CLONESTRING = $this->_validateText($this->text);
        $CLONESTRING = $this->text;
        foreach ($this->tagsToIgnore as $tag) {
            $re = $this->reTagPairs[$tag];
            preg_match_all($re, $CLONESTRING, $m);
            foreach ($m[0] as $found) {
                $token = '@[' . Token::create() . ']';
                $this->_cacheFoundTag($found, $token);
                $CLONESTRING = str_replace($found, $token, $CLONESTRING);
            }
        }
        $CLONESTRING = $this->_validateText($CLONESTRING);
        // $CLONESTRING = htmlentities($CLONESTRING);

        return $this->_injectIgnoredTags($CLONESTRING);
    }


    private function _injectIgnoredTags($str)
    {
        foreach ($this->ignoredTagPairs as $pair) {
            extract($pair);
            $str = str_replace($token, $html, $str);
        }
        return $str;
    }


    private function _cacheFoundTag($found, $token)
    {
        $this->ignoredTagPairs[] = [
            'token' => $token,
            'html' => $found,
        ];
    }
}
