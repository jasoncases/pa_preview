<?php

namespace Proaction\Domain\Timesheets\Resource\EmailActions;

use Proaction\Domain\Employees\Model\Employee;
use Proaction\System\Resource\Email\EmailActions\EmailAction;

class TimesheetEmailAction extends EmailAction
{
    protected $includeAuthor = false;
    protected $addAdmin = true;
    protected $moduleName = 'Timesheets';

    /**
     * Generic get message from EmailTemplate and parse via the Temlpat-
     * er class with the provided data. Data is automatically merged w/
     * the author and client arrays.
     *
     * author array: [email, displayName, authorId]
     *
     * @param array $model
     * @return string
     */
    protected function _getMessage($model)
    {
        $message = view('Domain.Timesheets.Email.' . $this->templateName, $model)->render();
        return $message;
    }

    protected function _extendInit()
    {
        $this->_setSubscribers();
    }

    protected function _setSubscribers()
    {
        // in general these will be sent to client admins, so by default
        // this method will only add admin, and if any specific ts email
        // require the author, those can be added in later
        if ($this->addAdmin) {
            $this->subscribers = $this->_getAdmin();
        }
    }

    private function _getAdmin()
    {
        $admin = Employee::getAdmin();
        return $admin->pluck('email')->toArray();
    }
}
