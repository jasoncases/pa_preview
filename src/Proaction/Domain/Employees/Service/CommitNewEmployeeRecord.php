<?php

namespace Proaction\Domain\Employees\Service;

use Proaction\Domain\Employees\Resource\EmployeeMod;

class CommitNewEmployeeRecord
{
    private $empObj;

    public function __construct($empObj)
    {
        $this->empObj = $empObj;
    }

    public function commit()
    {
        return EmployeeMod::createNewEmployee($this->empObj);
    }
}
