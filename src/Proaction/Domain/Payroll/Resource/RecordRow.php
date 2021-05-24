<?php

namespace Proaction\Domain\Payroll\Resource;

use Proaction\Domain\Display\DisplayVersionTwo;

class RecordRow
{
    protected $_displayKeys = ['id', 'first_name', 'last_name', 'total_pay', 'total_hours', 'reg_pay', 'reg_hours', 'rate', 'overtime_hours'];
    protected $_shifts = [];
    protected $_offset;

    public function __construct(array $data)
    {
        $this->_data = $data;
    }

    public function render()
    {
        $display = new DisplayVersionTwo();
        $display->setLayout('empty');
        $display->setFilePath('View/payroll/row.html');
        $display->setData($this->_data);
        $display->render();
    }

    public function renderClose()
    {
        echo '</div> <!-- What does this CLOSE?? (1) [PR-EDIT-CONTAINER]-->';
        echo '</div> <!-- What does this CLOSE??  (2) [PAYROLL-SHIFT-CONTAINER]-->';
    }
}
