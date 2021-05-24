<?php

namespace Proaction\Domain\Users\Controller;

use Proaction\Domain\Users\Service\CurrentShiftHours;
use Proaction\System\Controller\BaseProactionController;

class UserHoursController extends BaseProactionController {

    public function index (){
        $employeeId = $this->user->get("employeeId");
        ini_set('serialize_precision', -1);

        $this->status->aux(
            [
                'daily' => CurrentShiftHours::getCurrentDay($employeeId),
                'weekly' => CurrentShiftHours::getCurrentWeek($employeeId),
                'monthly' => CurrentShiftHours::getCurrentMonth($employeeId),
                ]
                // $this->_test()
        )->echo();
    }

    private function _test() {
        return [
            'daily' => $this->_testDaily(),
            'weekly' => $this->_testWeekly(),
            'monthly' => $this->_testMonthly(),
        ];
    }

    private function _testDaily(){
        return [
            'total' => 12.00,
            'total_paid' => 24.35,
            'regular' => 89.90,
            'overtime' => 121.80,
            'break' => 12.38,
            'lunch' => 12.09,
        ];
    }
    private function _testWeekly(){
        return [
            'total' => 13.70,
            'total_paid' => 13.70,
            'regular' => 13.70,
            'overtime' => 13.70,
            'break' => "13.0",
            'lunch' =>13.70 ,
        ];
    }
    private function _testMonthly(){
        return [
            'total' => 1.20,
            'total_paid' => 1.20,
            'regular' =>1.20 ,
            'overtime' =>1.20 ,
            'break' =>1.20 ,
            'lunch' => 1.20,
        ];
    }
}