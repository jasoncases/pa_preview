<?php

namespace Exception;

class PinThrottleException extends Exception
{
    protected $message = 'Failed pin attempts exceeded. Please wait.';
};