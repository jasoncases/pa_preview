<?php

namespace Proaction\Resource\Payroll;

use Proaction\Domain\Display\DisplayVersionTwo;

class Summary
{
    public function __construct(array $summary)
    {
        $this->details = $summary;
    }

    public function render()
    {
        $this->_hourSummary();
    }

    private function _hourSummary()
    {
        echo "\n\n\n<!-- BEGIN SUMMARY CONTAINER -->\n\n\n";

        echo '<div class="flex-row flex-center pr-summary-container" id="payroll-hour-detail-{id}">';
        echo "\n\n\n";
        $this->_renderSummary();
        echo "\n\n\n<!-- END SUMMARY CONTAINER -->\n\n\n";
    }

    private function _renderSummary()
    {
        $display = new DisplayVersionTwo();
        $display->setLayout('empty');
        $display->setFilePath('View/payroll/prweek.html');
        $display->setData($this->details);
        $display->render();
        // return new \Proaction\Resource\Display\DisplayVersionTwo('View/payroll/prweek.html', $this->details);
    }
}
