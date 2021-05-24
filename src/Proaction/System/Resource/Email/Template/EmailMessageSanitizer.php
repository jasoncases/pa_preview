<?php

namespace Proaction\System\Resource\Email\Template;


class EmailMessageSanitizer
{
    private $img_re = '/(<img.+?>)/';
    private $video_re = '/(<video.*?<\/video>)/';
    private $img_alt_re = '/alt="(.*)"/';
    private $anchor_re = '/(<a.*?<\/a>)/';

    public static function clean($message, $ticket_id, $client)
    {
        return (new static)->_clean($message, $ticket_id, $client);
    }

    public static function remove($message){
        return (new static)->_findAndRemove($message);
    }

    private function _clean($message, $ticket_id, $client)
    {
        $message = $this->_findAndReplaceImageTags($message, $ticket_id, $client);
        $message = $this->_findAndReplaceVideoTags($message, $ticket_id, $client);
        return $message;
    }

    private function _findAndReplaceImageTags($message, $ticket_id, $client)
    {
        preg_match_all($this->img_re, $message, $match);
        foreach ($match[1] as $m) {
            $alt = $this->_getImageAlt($m);
            $message = str_replace($m, $this->_insertImagePlaceholder($alt, $ticket_id, $client), $message);
        }
        return $message;
    }

    private function _findAndReplaceVideoTags($message, $ticket_id, $client)
    {
        preg_match_all($this->video_re, $message, $match);
        foreach ($match[1] as $m) {
            $message = str_replace($m, $this->_insertVideoPlaceholder($ticket_id, $client), $message);
        }
        return $message;
    }

    private function _insertVideoPlaceholder($ticket_id, $client)
    {
        return '[ User video placeholder <a href="https://' . $client . '.zerodock.com/tickets/' . $ticket_id . '">Click to view</a> ]<br />';
    }
    private function _insertImagePlaceholder($alt, $ticket_id, $client)
    {
        return '[ User image placeholder (' . $alt . ') <a href="https://' . $client . '.zerodock.com/tickets/' . $ticket_id . '">Click to view</a> ]<br />';
    }

    private function _getImageAlt($imgTag)
    {
        preg_match($this->img_alt_re, $imgTag, $m);
        return $m[1];
    }

    private function _findAndRemove($str){
        return preg_replace(
            $this->img_re, "", preg_replace(
                $this->video_re, '', preg_replace(
                    $this->anchor_re, "", $this->_removeWhiteSpace($str)
                )
            )
        );
    }

    private function _removeWhiteSpace($str){
        $str = str_replace("\r", "", $str);
        $str = str_replace("\n", "", $str);
        $str = str_replace("<br>", "", $str);
        $str = str_replace("<br />", "", $str);
        return $str;
    }
}
