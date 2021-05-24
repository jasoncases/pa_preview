<?php

namespace Proaction\Domain\Employees\Resource;

use Illuminate\Http\Request;
use Proaction\Domain\Employees\Controller\PendingEmployeeController;
use Proaction\Domain\Employees\Model\PendingEmployee;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Helpers\Misc;

class PendingBuilder
{

    public $employee;
    public $progress;

    public function create(array $props)
    {
        PendingValidator::validate($props);
        $this->employee = PendingEmployee::p_create($props);
        $this->_setProgress(1);
    }

    public function update(array $props)
    {
        PendingValidator::validate($props);
        $this->employee = PendingEmployee::p_update($props);
        $this->_determineProgress();
    }

    public function review($id)
    {
        $this->employee = PendingEmployee::getReview($id);
        $this->_determineProgress();
    }

    public function get($id)
    {
        $this->employee = PendingEmployee::find($id);
        $this->employee->rate = Misc::money($this->employee->rate);
        $this->_determineProgress();
    }

    private function _determineProgress()
    {
        if (!is_null($this->employee->permission_id)) {
            $this->_setProgress(3);
        } else if (!is_null($this->employee->rate)) {
            $this->_setProgress(2);
        } else if (!is_null($this->employee->email)) {
            $this->_setProgress(1);
        } else {
            $this->_setProgress(0);
        }
    }

    private function _setProgress($val)
    {
        $this->progress = $val;
    }
}
