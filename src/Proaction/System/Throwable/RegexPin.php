<?php

namespace Exception;

class RegexPin extends Exception
{
    public $message = "Regex Exception: Incorrect pin format. Must be (4) numeric characters.";
}