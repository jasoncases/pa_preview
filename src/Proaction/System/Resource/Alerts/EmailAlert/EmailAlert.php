<?php

namespace Proaction\Resource\Alert;

use Proaction\Resource\Email;
use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\System\Helpers\Arr;
use Proaction\System\Helpers\Str;

/**
 * Child class of Alert(), sends email alerts
 */
class EmailAlert extends Alert
{
    protected $type = 'email';


    /**
     * Break the Alert->template into it's component subject/message
     * parts and pass to the _sendEmail method
     *
     * @return bool
     */
    protected function _send($expiration = 0): bool
    {
        extract(json_decode($this->template, true));
        $subject = \Proaction\Resource\Templater::parse($subject, $this->data);
        $message = \Proaction\Resource\Templater::parse($message, $this->_formatData());
        return $this->_sendEmail($subject, $message);
    }

    private function _formatData()
    {
        $max_hours = number_format(current(\Proaction\Model\Meta\ClientGlobal::get('unix_auto_clockout')) / 3600, 2, '.', '');
        return array_merge(compact('max_hours'), $this->data);
    }

    /**
     * Sned the email
     *
     * @param string $subject 
     * @param [type] $message
     * @return bool 
     */
    private function _sendEmail(string $subject, $message): bool
    {
        mail(ProactionUser::defaultAdminEmail(), 'TESTING AN EMAIL ALERT', print_r($this, true));
        // TODO - return the auxHeadersCopyAdmin version.
        return Email::to($this->data['email'])
            ->ccAdmin()
            ->subject($subject)
            ->message($message)
            ->compose();
    }
}
