<?php

namespace Exception;

class NonUniquePinException extends Exception {
    protected $message = "Supplied access PIN already in use - Please try another value";
};