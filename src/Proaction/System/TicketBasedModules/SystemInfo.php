<?php 

namespace Proaction\System\TicketBasedModules;

use Proaction\System\Resource\Session\SystemSession;
use Proaction\System\Resource\Status\Status;

class SystemInfo {
    private $session;
    private $status;

    private $map = [
        'task' => [
            'status' => '\Proaction\Model\Tasks\TaskStatus',
            'category' => '\Proaction\Model\Tasks\TaskCategory',
        ], 
        'ticket' => [
            'status' => '\Proaction\Model\Ticket\TicketStatus',
            'category' => '\Proaction\Model\Ticket\TicketCategory',
        ],
        'code' => [
            'status' => '\Proaction\Model\Codes\CodeStatus',
            'category' => '\Proaction\Model\Codes\CodeCategory',
        ],
    ];

    public static function get($module, $key) {
        return (new static)->_get($module, $key);
    }

    public function __construct() {
        $this->_init();
    }

    private function _get($module, $key) {
        $module = rtrim($module, 's');
        $data = $this->session->pluck($module);
        if (!isset($data[$key])) {
            $data[$key] = $this->_acquireData($module, $key);
            $this->session->add($module, $data);
        } 
        $this->status->aux($data[$key])->echo();
    }

    private function _acquireData($module, $key) {
        $cls = $this->map[$module][$key];
        return $cls::all();
    }

    private function _init() {
        $this->session = new SystemSession();
        $this->status = new Status();
    }
}