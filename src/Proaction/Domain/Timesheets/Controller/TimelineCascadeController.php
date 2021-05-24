<?php

namespace Proaction\Domain\Timesheets\Controller;

use Proaction\Domain\Timesheets\Model\Shift;
use Proaction\Domain\Timesheets\Resource\Timeline\Cascade;
use Proaction\System\Controller\BaseProactionController;

class TimelineCascadeController extends BaseProactionController
{
    public function __invoke($id, $date)
    {
        $shifts = Shift::getAllShiftsByDateAndEmployeeId($date, $id);
        $cascade = new Cascade($shifts);
        return $cascade->render();
    }
}
