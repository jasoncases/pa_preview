<?php

namespace Exception;

class RecoveryTokenHasExpired extends Exception
{
    protected $message = 'Password Recovery Token has expired.';
};
