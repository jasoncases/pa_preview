<?php 

namespace Proaction\System\TicketBasedModules;

class Async{

    protected $actionContainer = [];
    protected $action;
    protected $data;

    public function __construct($action, $data)
    {
        $this->action = $action;
        $this->data = $data;
    }

    public function run() {

    }
    
    protected function _success() {}
    protected function _failure() {}
}