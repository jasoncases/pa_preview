<?php

namespace Proaction\Domain\Employees\Service;

use Proaction\Domain\Employees\Model\PendingEmployee;
use Proaction\Domain\Meta\Service\CommitProactionMetaUser;
use Proaction\Domain\Users\Service\CommitNewClientUser;

/**
 * Creates all necessary Employee/User records.
 * 
 * Users and Employees are disparate models within Proaction. This was 
 * done because there was the possiblity that there would be users that
 * were *not* employees, i.e., contractors, consultants, etc, that may
 * or may not need different permissions and rulesets. 
 * 
 * CreateNewEmployee::class creates the following:
 * 
 * 1.) ProactionMeta User record - a row in the meta_users table of the
 *     `proaction_meta` database. This is an entry point for users that
 *      hit the www.zerodock.com endpoint. It stores the user's email w/
 *      an association with the Client UID, which, after successful log
 *      will send the user to their appropriate sub-domain.
 * 2.) Client User record - an email/password association record with a
 *      unique id#. The user number is a foreign key to the `employees`
 *      table that may, or may not, match the employees.id field
 * 3.) Client Employee record - a table of employee personal info, name,
 *      email, phone, etc
 * 4.) All secondary data for the new employee, department, payrate, etc
 */
class CreateNewEmployee
{
    private $pending_id;

    public function __construct($id)
    {
        $this->pending_id = $id;
    }

    public static function create($pending_id)
    {
        return (new CreateNewEmployee($pending_id))->_create();
    }

    private function _create()
    {
        $this->_createProactionMetaUser();
        $user_id = $this->_createClientUser();
        $employee = $this->_createClientEmployee($user_id);
        $this->_createSecondaryEmployeeRecords($employee->id);
        return true;
    }

    private function _createProactionMetaUser()
    {
        $email = PendingEmployee::where('id', $this->pending_id)->first('email');
        $metaCreate = new CommitProactionMetaUser($email->email);
        return $metaCreate->commit();
    }

    private function _createClientUser()
    {
        $userProps = PendingEmployee::where('id', $this->pending_id)->first(['email', 'pin']);
        $userCreate = new CommitNewClientUser($userProps);
        return $userCreate->commit();
    }

    private function _createClientEmployee($user_id)
    {
        $empProps = PendingEmployee::where('id', $this->pending_id)->first();
        $empProps->user_id = $user_id;
        $empCreate = new CommitNewEmployeeRecord($empProps);
        return $empCreate->commit();
    }

    private function _createSecondaryEmployeeRecords($employee_id)
    {
        $secProps = PendingEmployee::where('id', $this->pending_id)
            ->first(
                [
                    'permission_id',
                    'department_id',
                    'status_id',
                    'rate',
                    'allow_remote_timesheet_action'
                ]
            );

        return new CommitSecondaryProps($employee_id, $secProps);
    }
}
