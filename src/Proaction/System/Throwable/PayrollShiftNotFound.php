<?php

namespace Exception;

class PayrollShiftNotFound extends Exception
{
    protected $message = "No existing shift record found via the provided id";
};
