<?php

namespace Proaction\System\TicketBasedModules;

use Exception\IllegalValueException;

class GuestUser {
    private $module, $email;
    private $srcMap = [
        'task' => '\Proaction\Model\Tasks\TaskUser',
        'ticket' => '\Proaction\Model\Ticket\TicketUser',
        'code' => '\Proaction\Model\Codes\CodeUser',
    ];
    public function __construct($module, $email)
    {
        $this->module = $this->_validateModule($module);
        $this->email = $email;
    }

    public function go() {
        $ssalc = $this->srcMap[$this->module];
        return $ssalc::save([
            'name' => $this->email,
            'email' => $this->email,
            'displayName' => $this->email,
            'status' => 1,
        ]);
    }

    private function _validateModule($module) {
        $module = rtrim($module, 's');
        if (!array_key_exists($module, $this->srcMap)) {
            throw new IllegalValueException('Provided module missing from srcmap');
        }
        return $module;
    }
}