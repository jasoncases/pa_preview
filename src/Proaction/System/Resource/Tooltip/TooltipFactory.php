<?php

namespace Proaction\System\Resource\Tooltip;

use Proaction\System\Model\Tooltip\Tooltip;
use Proaction\System\Resource\Session\SystemSession;
use Proaction\System\Resource\Templater\Templater;

class TooltipFactory
{

    private $path = '/home/zerodock/{filesystem}/View/template/system/tooltip.html';

    public function __construct()
    {
        $this->path = $this->_buildPath();
    }

    private function _buildPath()
    {
        return Templater::parse($this->path, (new SystemSession())->pluck('config'));
    }

    public static function create($key)
    {
        return (new static)->_create($key);
    }

    private function _create($key)
    {
        $this->key = $key;
        return $this->_generateTooltip();
    }

    private function _generateTooltip()
    {
        $template = file_get_contents($this->path);
        return Templater::parse($template, [
            'tooltip' => Tooltip::getByName($this->key),
        ]);
    }
}
