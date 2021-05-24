<?php

namespace Exception;

class TimesheetOverlapException extends Exception
{
    protected $message = 'Overlapping timestamps not allowed. Please wait 30 seconds and try again.';
};
