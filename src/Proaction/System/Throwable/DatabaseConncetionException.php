<?php

namespace Exception;

class DatabaseConnectionException extends Exception {
    protected $message = "Database connection not found";
};