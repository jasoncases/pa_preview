<?php

namespace Proaction\Domain\Timesheets\Service;

class Action {

    public static function create($employee_id, $action_id, $comment = false) {
        return (new static)->_create($employee_id, $action_id, $comment);
    }

    private function _create($employee_id, $action_id, $comment) {
        //
    }
}
