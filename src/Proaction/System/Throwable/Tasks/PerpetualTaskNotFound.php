<?php

namespace Exception;

class PerpetualTaskNotFound extends Exception
{
    protected $message = 'Proaction did not find a perpetual task matching the provided task id';
};
