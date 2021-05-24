<?php

namespace Proaction\System\Resource\Session;

use Proaction\System\Resource\Config\ProactionConfig;

class SystemSession extends BaseSessionHandler
{

    protected $name = 'system';

    public function __construct()
    {
        parent::__construct();
        $this->_getProactionConfig();
    }

    private function _getProactionConfig()
    {
        // echo 'building config';
        // Arr::pre($this->pluck('config'));
        // Arr::pre($_SESSION);
        if (!$this->pluck('config')) {
            $this->add('config', ProactionConfig::all());
        }
    }
}
