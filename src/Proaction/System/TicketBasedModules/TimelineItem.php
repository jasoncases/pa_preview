<?php

namespace Proaction\System\TicketBasedModules;

use Exception\MissingFileException;
use Proaction\Domain\Display\DisplayVersionTwo;
use Proaction\System\Resource\Config\ProactionConfig;

class TimelineItem {

    protected $pathroot = '/View/';
    
    public function __construct($template, $data, $layout = null)
    {
        $this->filepath = $this->_validateFile($template);
        $this->data = $this->_formatData($data);
        $this->layout = $layout;
    }

    /**
     * Any data munging that needs to happen to the incoming timeline
     * data has to happen here
     *
     * @param [type] $data
     * @return void
     */
    private function _formatData($data){
        $data['date'] = ucfirst(strtolower($data['date']));
        return $data;
    }

    public function generate() {
        $display = new DisplayVersionTwo();
        $display->setLayout($this->layout ?: 'empty');
        $display->setFilepath($this->filepath);
        $display->setData($this->data);
        return $display->render(false);
    }

    private function _validateFile($filename) {
        $path = $this->pathroot . $filename;
        $filesystem = ProactionConfig::get('filesystem');
        if (!file_exists("/home/zerodock/$filesystem".$path)) {
            throw new MissingFileException("$path not found");
        }
        return $path;
    }
}