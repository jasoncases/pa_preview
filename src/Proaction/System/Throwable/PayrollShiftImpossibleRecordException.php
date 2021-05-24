<?php

namespace Exception;

class PayrollShiftImpossibleRecordException extends Exception
{
    protected $message = "Provided records are missing a shift segement punch";
};
