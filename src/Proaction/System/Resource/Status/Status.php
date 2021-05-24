<?php

namespace Proaction\System\Resource\Status;

class Status
{

    public $status = 'success';
    public $message = '';
    private $output = [];

    public function __construct()
    {
    }

    function echo()
    {
        // $this->status;
        // $this->output['message'] = $this->message;
        // debug_print_backtrace();
        echo json_encode($this->output);
    }

    public function status($status = 'success')
    {
        $this->status = $status;
        http_response_code(200);
        return $this;
    }

    public function message($message = '')
    {
        $this->message = $message;
        return $this;
    }
    public function aux($array)
    {
        $this->output = $array;
        return $this;
    }
    public function error()
    {
        $this->status = 'error';
        http_response_code(206);
        $this->echo();
    }
}

/**
 * ! usage
 * $status = new Status();
 *
 * ! if no change to defaults, i.e., status = success
 * $status->echo();
 *
 * ! status = error
 * $status->status('error')->echo();
 *
 * ! status = success with message
 * $status->message('testing message')->echo();
 *
 */
