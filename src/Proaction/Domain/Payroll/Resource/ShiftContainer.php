<?php

namespace Proaction\Domain\Payroll\Resource;

class ShiftContainer
{

    private $_employee;
    public function __construct($employee)
    {
        $this->_employee = $employee;
    }

    public function render()
    {

        echo "\n\n\n<!-- BEGIN SHIFT ACTION CONTAINER -->\n\n\n";

        echo '<div class="flex-row flex-evenly flex-1 pr-shift-detail-container" id="payroll-shift-detail-' . $this->_employee['id'] . '">';
        echo '  <div class="flex-col flex-start flex-1 pr-edit-container">';
        echo '<div class="spinner-container" style="padding: 40px;background-color:white;">
                <div class="lds-spinner">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                </div>
            </div>';

        echo "\n\n\n<!-- END SHIFT ACTION CONTAINER -->\n\n\n";
    }
}
