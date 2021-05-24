<?php

namespace Proaction\Domain\Timesheets\Controller;

use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\Domain\Permissions\Resource\PermissionCheck;
use Proaction\Domain\Timesheets\Model\Shift;
use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\Domain\Timesheets\Model\TimesheetCommentChain;
use Proaction\Domain\Timesheets\Model\TimestampView;
use Proaction\Domain\Timesheets\Resource\TimesheetActionFilter;
use Proaction\Domain\Timesheets\Resource\Updater;
use Proaction\System\Controller\BaseProactionController;
use Proaction\System\Views\GeneralView;
// use Proaction\System\Views\HomeIndexView;


// overwrite global time() for testing
function time()
{
    return TimesheetController::$now ?? \time();
}

class TimesheetController extends BaseProactionController
{
    public static $now;
    protected $linkId = 2;
    protected $logAccess = true;

    /**
     *
     */
    public function index()
    {
        return view('Domain.Timesheets.index', GeneralView::add());
    }

    private function _action()
    {
        return new TimesheetActionFilter();
    }
    /**
     *
     *
     */
    public function store()
    {
        try {


            $this->_action()->punch($this->props->employee_id, $this->props->activity_id);

            if ($this->id != $this->props->employee_id) {
                // current user is creating the action for another user
                $rec = Timesheet::getLast();
                TimesheetCommentChain::p_create([
                    'timesheet_id' => $rec['id'],
                    'shift_id' => $rec['shift_id'],
                    'comment' => 'Manual timestamp created',
                ]);
            }


            $this->status->aux(['status' => 'success'])->echo();
        } catch (\Exception $e) {
            $this->message($e->getMessage(), 'error');
            $this->status->aux(['msg' => $e->getMessage()])->error();
        }
    }


    /**
     *
     *
     */
    public function putReviewedStatus()
    {
        print_r($this->props);
        $stamp_ids = json_decode($this->props->stamp_ids, true);
        foreach ($stamp_ids as $id) {
            Timesheet::p_update(['id' => $id, 'activity_flag' => $this->props->state]);
            $this->_addReviewStatusComment($id, $this->props->state);
            // TODO - add reset for doublecheck status if switching alert status to 'unreveiwed'
        }
    }

    private function _addReviewStatusComment(int $timesheet_id, string $state)
    {
        $comment = "Changed reviewed status of $timesheet_id to $state";
        $shift_id = Timesheet::where('id', $timesheet_id)->get('shift_id');
        return TimesheetCommentChain::p_create(compact('comment', 'timesheet_id', 'shift_id'));
    }

    /**
     * The page for creating new users
     *
     */
    public function create()
    {
    }

    /**
     *
     */
    public function show()
    {
    }

    /**
     *
     */
    public function update()
    {
        try {
            PermissionCheck::validate('allow_edit_payroll');

            $this->_update()->process();
            $this->status->aux(['status' === 'success'])->echo();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    private function _update()
    {
        return new Updater($this->props, $this->user);
    }

    /**
     *
     */
    public function edit()
    {
        $this->render('show.html');
    }
    /**
     *
     */
    public function destroy()
    {
        //
    }

    /**
     *  BEGIN CONTROLLER METHODS
     * --------------------------------------------------------------------------------------------------------------
     *
     */

    public function getDataByDate()
    {
        // * route: timesheet/bydate

        $emp = (array) EmployeeView::getActiveEmployees(['id as empid', 'first_name as name']);
        $date = $this->props->date ?? date('Y-m-d');

        $breakId = 3;
        $lunchId = 5;

        $c = [];
        foreach ($emp as $e) {
            extract($e);
            $actions = $this->_getActionsForSnapshot($date, $empid) ?? [];
            $actionCounts = array_count_values(array_column($actions, 'activity_id'));
            $breakOne = $actionCounts[$breakId] >= 1;
            $breakOneActive = $actionCounts[-$breakId] < 1 && boolval($breakOne);
            $breakTwo = $actionCounts[$breakId] == 2;
            $breakTwoActive = $actionCounts[-$breakId] == 1 && boolval($breakTwo);
            $lunch = $actionCounts[$lunchId] == 1;
            $lunchActive = $actionCounts[-$lunchId] < 1 && boolval($lunch);
            $shiftActive = $this->_shiftIsActive($actionCounts);

            $combine = compact('shiftActive', 'breakOne', 'breakOneActive', 'breakTwo', 'breakTwoActive', 'lunch', 'lunchActive');
            $sort = $this->_getSortOrder($combine);
            $c[] = array_merge($e, $combine, compact('actions'), compact('sort'));
        }

        array_multisort(array_column($c, 'sort'), SORT_DESC, $c);

        $this->status->aux($c)->echo();
    }

    private function _shiftIsActive($actionCounts)
    {
        $clockIn =  $actionCounts[1];
        $clockOut = $actionCounts[0];
        return $clockIn > $clockOut;
    }

    public function getBreakStatus($id)
    {

        return Timesheet::getCurrentBreakCount($id);
    }

    /**
     * TEST METHOD
     *
     */
    public function getTest()
    {
    }

    /**
     * Explicitly define the sort order of certain states for the
     * Snapshot component
     *
     * @param array $actionArray
     * @return void
     */
    private function _getSortOrder($actionArray)
    {
        extract($actionArray);

        if (!$shiftActive) {
            return 0;
        }

        if ($breakOneActive || $breakTwoActive || $lunchActive) {
            return 10;
        }

        if ($breakOne && $breakTwo && $lunch) {
            return 8;
        }

        if ($breakOne && $breakTwo || $lunch) {
            return 6;
        }

        if ($breakOne && !$breakTwo && !$shiftActive) {
            return 5.5;
        }

        if ($breakOne && !$breakTwo) {
            return 5;
        }

        if ($shiftActive && $breakOne && !$lunch) {
            return 4;
        }

        if ($shiftActive && !$breakOne && !$lunch) {
            return 2;
        }

        if ($shiftActive) {
            return 2;
        }

        return 0;
    }

    private function _getActionsForSnapshot($date, $employeeId)
    {
        $shifts = Shift::where('employee_id', $employeeId)
            ->andWhere('active', 1)
            ->get('id');
        return empty($shifts)
            ? []
            :  TimestampView::whereIn('shift_id', $shifts->pluck('id')->toArray())->get();
    }
}
