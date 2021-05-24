<?php

namespace Proaction\System\Resource\Email\Template;

use Proaction\System\Resource\Session\SystemSession;
use Proaction\System\Resource\Templater\Templater;

class EmailTemplate
{
    private $_path = '/home/zerodock/{filesystem}/View/template/email/';

    public function __construct()
    {
        $this->_path = $this->_buildPath();
    }

    public static function load($filename)
    {
        try {
            return (new static)->_load($filename);
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    private function _buildPath()
    {
        return Templater::parse($this->_path, (new SystemSession())->pluck('config'));
    }

    private function _load($filename)
    {
        $path = $this->_path . $filename . '.html';
        if (!file_exists($path)) {
            throw new \Exception("Email template file [$path] does not exist.");
        }
        return file_get_contents($path);
    }
}
