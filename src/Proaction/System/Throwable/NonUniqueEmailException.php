<?php

namespace Exception;

class NonUniqueEmailException extends Exception {
    protected $message = "Employee email values must be unique - Provided email already exists";
};