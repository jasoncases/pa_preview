<?php

namespace Proaction\Resource\User;

use Proaction\Domain\Clients\Model\MetaUser;
use Proaction\Domain\Clients\Resource\ProactionClient;
use Proaction\Domain\Employees\Resource\EmployeeMod;
use Proaction\Domain\Users\Resource\Create;
use Proaction\System\Resource\Lib\Uid;

class MetaCreate
{

    private $data;

    public function __construct($employeeDataObj)
    {
        $this->data = $employeeDataObj;
    }

    public function process()
    {
        try {
            $this->data['user_id'] = $this->_createUserInstance();
            $this->data['employee_id'] = $this->_createEmployeeInstance();
            $this->_createMetaUserInstance();
            $this->_createSecondaryEmployeeRecords();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    private function _createUserInstance()
    {
        return Create::newUser(
            $this->data['email'],
            Uid::create('!Z', 10, 10, ''),
            $this->data['pin']
        );
    }

    private function _createEmployeeInstance()
    {
        return EmployeeMod::createNewEmployee($this->data);
    }

    private function _createMetaUserInstance()
    {
        return MetaUser::p_create([
            'email' => $this->data['email'],
            'client_uid' => ProactionClient::uid(),
        ]);
    }

    private function _createSecondaryEmployeeRecords()
    {
        extract($this->data);
        $dateOfHire = $this->data['date_of_hire'] ?? date('Y-m-d');
        EmployeeMod::insertEmployeePermissions($employee_id, $permission_id);
        EmployeeMod::insertEmployeeDepartment($employee_id, $department_id);
        EmployeeMod::insertEmployeeStatus($employee_id, $status_id);
        EmployeeMod::insertEmployeeDateOfHire($employee_id, $dateOfHire);
        EmployeeMod::insertEmployeeRate($employee_id, $rate);
    }
}
