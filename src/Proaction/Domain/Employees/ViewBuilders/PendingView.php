<?php

namespace Proaction\Domain\Employees\ViewBuilders;

use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Employees\Model\PendingEmployee;
use Proaction\System\Views\Base\BaseViewDomain;

class PendingView extends BaseViewDomain
{
    protected function _getViewData()
    {
        if (isset($_GET['pending_test'])) {
            $employee = new PendingEmployee;
            $employee->first_name = 'Testing';
            $employee->last_name = 'Atest';
            $employee->nickname = 'thisisanickname';
            $employee->phone = '9198675309';
            $employee->phonetic = 'mightyboosh';
            $employee->date_of_birth = '03/17/1983';
            $employee->email = 'thisisauniqueemail' . rand(0, 1000) . '@pendingtestligma.com';
        } else {
            $employee = null;
            if (isset($this->localData['pending_id'])) {
                $employee = PendingEmployee::find($this->localData['pending_id']);
            }
        }

        return [
            'employee' => $employee,
            'progress' => 0,
        ];
    }
}
