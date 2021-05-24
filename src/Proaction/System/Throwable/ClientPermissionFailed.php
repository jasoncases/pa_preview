<?php

namespace Exception;


class ClientPermissionFailed extends PermissionException{
    protected $message = 'Permission check failed - Please see an administrator';

    public function __construct($dest = '/')
    {
        $this->dest = $dest;
        parent::__construct($this->message, $dest);
    }
}
