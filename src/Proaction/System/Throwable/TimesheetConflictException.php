<?php

namespace Exception;

class TimesheetConflictException extends Exception
{
    protected $message = 'Conflicting timestamp provided. Please correct and resubmit.';
};
