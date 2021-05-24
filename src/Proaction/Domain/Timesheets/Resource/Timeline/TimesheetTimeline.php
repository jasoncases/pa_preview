<?php

namespace Proaction\Domain\Timesheets\Resource\Timeline;

use Proaction\Domain\Employees\Model\Employee;

class Timeline
{
    protected $min, $max, $current_status;

    protected $employee;

    public function __construct(Employee $employee)
    {
    }
}
