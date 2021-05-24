<?php

namespace Proaction\System\Views\Builder;

use Proaction\System\Resource\Data\Data;

class ViewBuilder {
    // build data & render
    protected $view;
    protected $module;
    protected $path = '/View/';
    protected $layout = 'layout';
    protected $data = [];

    public function __construct()
    {
        $this->data = $this->_buildData();
    }

    public function render() {
        return $this->_display()->_runAfter();
    }

    private function _display(){
        if (isset($_GET['tt'])) {
            // Arr::pre($this->_mergeControllerData());
            // Arr::pre(Data::getInstance());
            echo '<hr />';
        }
        // $this->_renderer()
        //     ->setLayout($this->layout)
        //     ->setFilepath($this->_buildPath())
        //     ->setData($this->_mergeControllerData())
        //     ->render();
        return $this;
    }

    private function _mergeControllerData() {
        return array_merge(Data::getInstance()->get(), $this->data) ?? [];
    }

    protected function _runAfter() {}

    private function _renderer() {
        // return new DisplayVersionTwo();
    }

    private function _buildPath() {
        return $this->path . $this->module . '/' . $this->view . '.html' ;
    }

    protected function _buildData() {}
}
