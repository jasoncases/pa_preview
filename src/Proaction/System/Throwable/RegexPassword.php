<?php

namespace Exception;

class RegexPassword extends Exception
{
    public $message = "Regex Exception: Incorrect password format. Must be 8+ char, at least one lowercase, uppercase, and one symbol (!@#$%*)";
}
