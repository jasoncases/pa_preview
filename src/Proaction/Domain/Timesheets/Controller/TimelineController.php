<?php

namespace Proaction\Domain\Timesheets\Controller;

use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Schedules\Model\ScheduleShift;
use Proaction\Domain\Tasks\Service\TaskCollectionSorter;
use Proaction\Domain\Timesheets\Resource\Timeline\TimelineBuilder;
use Proaction\Domain\Timesheets\Resource\Timeline\TimelineShifts;
use Proaction\Domain\Timesheets\Resource\TimesheetTimeline\TimesheetTimeline;
use Proaction\System\Controller\BaseProactionController;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Status\Status;

use function Proaction\System\Lib\getPayrollDateRange;

class TimelineController extends BaseProactionController
{

    public function __invoke($id)
    {
        // $order = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $tlb = new TimelineBuilder();
        (new Status())->aux($tlb->render())->echo();
    }
}
