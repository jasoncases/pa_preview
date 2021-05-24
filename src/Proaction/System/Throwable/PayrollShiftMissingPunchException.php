<?php

namespace Exception;

class PayrollShiftMissingPunchException extends Exception
{
    protected $message = "Provided records are missing a shift segement punch";
};
