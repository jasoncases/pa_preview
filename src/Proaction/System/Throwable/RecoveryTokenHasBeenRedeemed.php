<?php

namespace Exception;

class RecoveryTokenHasBeenRedeemed extends Exception
{
    protected $message = 'Password Recovery Token no longer exists.';
};