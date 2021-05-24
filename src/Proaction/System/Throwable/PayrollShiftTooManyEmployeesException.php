<?php

namespace Exception;

class PayrollShiftTooManyEmployeesException extends Exception
{
    protected $message = "Result set contains data from multiple employees";
};
