<?php

namespace Proaction\System\Resource\Helpers;

class LinkParser
{

    protected $linkRe = '/((http|ftp|https):\/\/)?([\w_-]+(??(??\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-;]*[\w@?^=%&\/~+#-;])?/';
    protected $cleanRe = '/((http|https|www)(.+))/';
    protected $safeRe = '/((\[a.+?\])([\w\W]+?)(\[\/a\]))/';
    protected $domainRe = '/[http|https:\/\/](.+?)[\/]/';
    protected $terminusRe = '/\/$/';

    protected $maxLenght = 100;

    protected $reIgnore = ['/user-uploads/'];

    public static function toSql($string)
    {
        // decode incoming text because it's uriencoded from the JS side
        // $string = html_entity_decode($string);
        return (new static)->_toSql($string);
    }
    public static function toRender($string)
    {
        return (new static)->_toRender($string);
    }

    private function _toSql($string)
    {
        return $this->_parseForLinks($string);
    }

    private function _parseForLinks($string)
    {
        // $matches = $this->_getLinkMatches($string);
        $__stringClone = $string;
        // foreach (array_unique($matches) as $match) {
        //     $__stringClone = str_replace($match, $this->_generateAnchorTag($match), $__stringClone);
        // }
        return $__stringClone;
    }

    private function _generateAnchorTag($string)
    {
        $url = $this->_finalFormatLink($string);
        $anchorText = $this->_formatAnchorText($url);
        $link = '<a class="ticket-body-link" href="' . $url . '" target="_blank" title="' . $url . '" >' . $anchorText . '</a>';
        return $link;
    }

    private function _toRender($string)
    {
        return $this->_parseForSafeCode($string);
    }

    private function _getLinkMatches($string)
    {
        try {

            preg_match_all($this->linkRe, $string, $m);
            $fullMatch = $m[0];
            $c = [];
            foreach ($fullMatch as $match) {
                if (!$this->_validateCleanlist($match)) {
                    continue;
                }
                preg_match($this->cleanRe, $match, $mm);
                $c[] = $mm[0];
            }
            return $c;
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    private function _validateCleanlist($matchString)
    {
        foreach ($this->reIgnore as $re) {
            if (preg_match($re, $matchString)) {
                return false;
            }
        }
        return true;
    }

    private function _generateSafeCodeURL($string)
    {
        $url = $this->_finalFormatLink($string);
        $anchorText = $this->_formatAnchorText($url);
        $link = '<a class="ticket-body-link" href="' . $url . '" target="_blank" title="' . $url . '" >' . $anchorText . '</a>';
        return $link;
    }

    private function _formatAnchorText($string)
    {
        // $string = $this->_extractDomainFromAnchorText($string);
        return $string;
    }

    private function _extractDomainFromAnchorText($string)
    {
        if (preg_match_all($this->domainRe, $string, $match)) {
            return $match[1][1] ?? $string;
        }
        return $string;
    }

    private function _finalFormatLink($url)
    {
        $url = $this->_padLeftHTTP($url);
        return $url;
    }

    private function _padLeftHTTP($string)
    {
        if (!preg_match('/^(http|https)/', $string)) {
            return "https://$string";
        }
        return $string;
    }

    private function _parseForSafeCode($string)
    {
        $matches = $this->_getSafeCodeMatches($string);
        $__stringClone = $string;
        foreach ($matches as $match) {
            $__stringClone = str_replace($match, $this->_generateLink($match), $__stringClone);
        }
        return html_entity_decode($__stringClone);
    }

    private function _getSafeCodeMatches($string)
    {
        preg_match_all($this->safeRe, $string, $m);
        $c = [];
        $fullMatch = $m[0];
        return $fullMatch ?? [];
    }

    private function _generateLink($string)
    {
        $arr = ['[' => '<', ']' => '>'];
        foreach ($arr as  $search => $replace) {
            $string = str_replace($search, $replace, $string);
        }
        return $string;
    }
}
