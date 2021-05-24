<?php

namespace Exception;

class PayrollShiftTooManyShiftsException extends Exception
{
    protected $message = "Result set contains data from multiple shifts";
};
