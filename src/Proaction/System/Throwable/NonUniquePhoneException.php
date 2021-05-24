<?php

namespace Exception;

class NonUniquePhoneException extends Exception {
    protected $message = "Employee phone values must be unique - Provided phone already exists";
};