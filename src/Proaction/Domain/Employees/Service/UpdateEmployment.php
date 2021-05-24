<?php

namespace Proaction\Domain\Employees\Service;

use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Employees\Model\EmployeeDepartment;
use Proaction\Domain\Employees\Model\EmployeeRate;
use Proaction\Domain\Employees\Model\EmploymentStatus;

class UpdateEmployment 
{
    /**
     * Data object for update operation
     * props:                   -   Model
     *      id (employee_id)
     *      status_id               (EmploymentStatus)
     *      rate (pay rate)         (EmployeeRate)
     *      department_id           (EmployeeDepartment)
     *      date_of_hire            (Employee)
     *
     * @var array
     */
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function commit() {
        extract($this->data);
        $this->_updateEmploymentStatus($id, $status_id);
        $this->_updateEmployeeRate($id, $rate);
        $this->_updateEmployeeDepartment($id, $department_id);
        $this->_updateEmployeeDateOfHire($id, $date_of_hire);
    }

    private function _updateEmploymentStatus($employee_id, $status_id) {
        // get record id by employee_id
        $id = EmploymentStatus::where('employee_id', $employee_id)
                ->latest('id')
                ->limit(1)
                ->get('id');
        // update with new info, or re-record with old info
        return EmploymentStatus::update(compact('id', 'employee_id', 'status_id'));
    }

    private function _updateEmployeeRate($employee_id, $rate) {
        // get last rate by employee_id, if diff, add new record
        $lastRate = (float) EmployeeRate::where('employee_id', $employee_id)
            ->latest('id')
            ->limit(1)
            ->get('rate');
        if ((float) $rate != $lastRate) { 
            return EmployeeRate::save(compact('employee_id', 'rate'));
        }
    }

    private function _updateEmployeeDepartment($employee_id, $department_id) {
        // get record id by employee_id
        $id = EmployeeDepartment::where('employee_id', $employee_id)->get('id');
        // update with new data, or re-record with old data
        return EmployeeDepartment::update(compact('id', 'employee_id', 'department_id'));
    }

    private function _updateEmployeeDateOfHire($id, $date_of_hire) { 
        // doh is stored in Employee table, so update
        return Employee::update(compact('id', 'date_of_hire'));
    }
}