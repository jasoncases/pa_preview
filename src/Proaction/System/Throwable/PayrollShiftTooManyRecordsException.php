<?php

namespace Exception;

class PayrollShiftTooManyRecordsException extends Exception
{
    protected $message = "Provided result set has too many state records";
};
