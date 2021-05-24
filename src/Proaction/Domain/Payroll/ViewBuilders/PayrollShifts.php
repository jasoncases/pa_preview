<?php

namespace Proaction\Domain\Payroll\ViewBuilders;

use DateTime;
use Proaction\Domain\Payroll\Resource\ReportBuilder\ShiftRangeBuilder;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Views\Base\BaseViewBuilder;

class PayrollShifts extends BaseViewBuilder
{

    protected function _getViewData()
    {
        $datetime = DateTime::createFromFormat('m-d-Y', $this->localData['from']);
        $from = $datetime->format('Y-m-d');
        $datetime = DateTime::createFromFormat('m-d-Y', $this->localData['to']);
        $to = $datetime->format('Y-m-d');
        $shiftBuilder = new ShiftRangeBuilder($from, $to, $this->localData['employee_id']);
        return [
            'shifts' => $shiftBuilder->get()
        ];
    }
}
