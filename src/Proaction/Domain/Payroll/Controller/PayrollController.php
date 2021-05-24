<?php

namespace Proaction\Domain\Payroll\Controller;

use Illuminate\Http\Request;
use Proaction\Domain\Employees\Model\Department;
use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\Domain\Payroll\ViewBuilders\PayrollReport;
use Proaction\Domain\Permissions\Resource\PermissionCheck;
use Proaction\Domain\Users\Resource\UserFactory;
use Proaction\System\Controller\BaseProactionController;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Views\GeneralView;

class PayrollController extends BaseProactionController
{
    protected $title = "Payroll Reports";

    /**
     *
     */
    public function index(Request $req)
    {

        $user = UserFactory::create();

        if (!PermissionCheck::validate('view_total_payroll', 'bool')) {
            $req->merge(['filterSearch' => $user->get('employeeId')]);
        }

        if (!$req->has('dates')) {
            $to = date('Y-m-d 23:59:59');
            $toJS = date('m/d/Y', strtotime($to));
            $from = $req->input('from') ?? date('Y-m-d 00:00:00', strtotime("-1 week"));
            $fromJS = date('m/d/Y', strtotime($from));
            $dates = "$fromJS - $toJS";
            // isset($this->props->dates)) {
        } else {
            $dates = explode('-', $req->input('dates'));
            $to = date('Y-m-d', strtotime(trim($dates[1])));
            $toJS = date('m/d/Y', strtotime($to));
            $from = date('Y-m-d', strtotime(trim($dates[0])));
            $fromJS = date('m/d/Y', strtotime($from));
        }

        $req->merge(compact('to', 'from', 'toJS', 'fromJS', 'dates'));

        if (isset($_GET['filterTest'])) {
            $req->merge(['filterSearch' => ['departmentSearchToggle' => true, 'departments' => [2]]]);
        }

        return view(
            'Domain.Payroll.index',
            PayrollReport::add(
                [],
                $req->all()
            )
        );

        // Arr::pre($req->all());
        // // Render the top portion of the payroll page, ie header, calendar, toggles
        // $this->render(
        //     'rendertest.html',
        //
        //     'payroll_top'
        // );

        // // Render the rest of the payroll information
        // $payroll = new \Proaction\Resource\Payroll\Payroll(
        //     $from,
        //     $to,
        //     explode(':', $this->props->filterSearch)
        // );
        // $payroll->load()->render();
    }

    /**
     * Public test method for various experimentation
     *
     * * route: /payroll/test
     *
     * @return void
     */
    public function getTest()
    {



        // Permission Check, non routing - returns a boolean
        $routeCode = "view_total_payroll";
        if (!$this->routePermissionAccess($routeCode)) {
            $this->props->filterSearch = 'employee_id:' . $this->user->get('employeeId');
        }

        $to = $this->props->to ?: date('Y-m-d 23:59:59');
        $from = $this->props->from ?: date('Y-m-d 00:00:00', strtotime("-1 week"));
        // Render the top portion of the payroll page, ie header, calendar, toggles
        $this->render(
            'rendertest.html',
            [
                'to' => date('M d, Y', strtotime($to)),
                'from' => date('M d, Y', strtotime($from)),
                'employees' => \Proaction\Model\Employee::where('status', 1)
                    ->get(
                        'CONCAT(first_name, " ", last_name) as displayName',
                        'id'
                    ),
                'departments' => \Proaction\Model\Department::all(),
            ],
            'payroll_top'
        );

        // Render the rest of the payroll information
        $payroll = new \Proaction\Resource\Payroll\Payroll(
            $from,
            $to,
            explode(':', $this->props->filterSearch)
        );
        $payroll->load()->render();
        Arr::pre($payroll);
    }

    /**
     * Load the data of a specific state, time, date, year, month, hour, etc, etc
     *
     * @return void
     */
    public function getStamp()
    {
        $stamp = \Proaction\Model\Timesheet::where('id', $this->props->id)->get('id', 'shift_id', 'DATE(time_stamp) as date', 'TIME(time_stamp) as time', 'YEAR(time_stamp) as year', 'MONTH(time_stamp) as month', 'DAY(time_stamp) as day', 'HOUR(time_stamp) as hour', 'MINUTE(time_stamp) as minute', 'SECOND(time_stamp) as second');
        echo json_encode($stamp);
    }

    /**
     *
     */
    public function store()
    {
        return new Redirect();
        // empty method, redirect
    }

    /**
     *
     */
    public function create()
    {
        return new Redirect();
        // empty method, redirect
    }

    /**
     *
     */
    public function show()
    {
        return new Redirect();
        // empty method, redirect
    }

    /**
     * Called by Payroll Module Actions.loadShifts()
     *
     * * route: payroll/shifts/{id}/{from:string}/{to:string}
     *
     * @return void
     */
    public function getShifts()
    {
        if (is_null($this->props->to)) {
            $this->props->to = date('Y-m-d 23:59:59');
        } else {
            $this->props->to = str_replace('-', '/', $this->props->to);
            $this->props->to = date('Y-m-d 23:59:59', strtotime($this->props->to));
        }
        if (is_null($this->props->from)) {
            $this->props->from = date('Y-m-d 00:00:00', strtotime("-1 week"));
        } else {
            $this->props->from = str_replace('-', '/', $this->props->from);
            $this->props->from = date('Y-m-d 00:00:00', strtotime($this->props->from));
        }

        $shifts = new \Proaction\Resource\Payroll\Shifts($this->props->from, $this->props->to, $this->props->id);
        $shifts->render();
    }

    /**
     * Redirect method if a shift is requested without explicitly stating to/from values
     *
     * @return void
     */
    public function getShiftsRedirect()
    {
        $this->getShifts();
    }

    /**
     *
     */
    public function postAddComment()
    {
        if (!\Proaction\Model\TimesheetCommentChain::newComment($this->props->comment, $this->props->timesheet_id, $this->user->get('employeeId'))) {
            $this->status->error();
            die();
        }
        $this->status->echo();
    }

    /**
     *
     */
    public function postLocationIp()
    {

        if (\Proaction\Model\Meta\ClientIpWhitelist::save(['ip_add' => $this->props->ip])) {
            $this->message('IP Address saved to company whitelist');
            $this->status->echo();
        } else {
            $this->message('Error whitelisting IP address - Please try again later');
            $this->status->error();
        }
    }

    /**
     *
     */
    public function destroy()
    {
        // empty method, redirect
        return new Redirect();
    }

    /**
     *
     */
    public function putUpdateManualFlag()
    {
        // * route: payroll/updateflag/{id}
        $stamp_ids = json_decode($this->props->stamp_ids, true);
        foreach ($stamp_ids as $id) {
            $this->_updateManualFlag($id, $this->props->status);
        }
    }

    /**
     * Update manual flags based on id
     *
     * @param  integer $timesheet_id
     * @param  string  $status
     * @return void
     */
    private function _updateManualFlag(int $timesheet_id, string $status)
    {
        $id = \Proaction\Model\TimesheetManualAlert::where('timesheet_id', $timesheet_id)->get('id');
        return \Proaction\Model\TimesheetManualAlert::update(compact('id', 'status'));
    }

    /**
     *
     */
    public function postCreateManualFlag()
    {
        $stamp_ids = json_decode($this->props->stamp_ids, true);
        foreach ($stamp_ids as $id) {
            $this->_createManualFlag($id);
        }
    }

    /**
     * Undocumented function
     *
     * @param  [type] $timesheet_id
     * @return void
     */
    private function _createManualFlag($timesheet_id)
    {
        $shift_id = \Proaction\Model\Timesheet::where('id', $timesheet_id)->get('shift_id');
        \Proaction\Model\TimesheetManualAlert::save(compact('timesheet_id', 'shift_id'));
    }

    /**
     *
     * ==================================================================================
     * ! These methods are from first run getting data. Need to recreate and wrap into new
     * ! module architecture.
     *   ================================================================================
     */

    /**
     *
     */
    private function returnStatus()
    {
        $q = 'SELECT cwt.identifier, ABS(a.activity_id) as absact, cwt.bar_color, a.employee_id, a.time_stamp, a.unix_ts, a.activity_id, b.last_action, cwt.actionId, e.first_name, e.last_name,
                CASE a.activity_id
                    WHEN 3 THEN "A"
                    WHEN 5 THEN "B"
                    WHEN 0 THEN "Z"
                    ELSE "C" END as sortOrder
                FROM ts_timesheet as a
                JOIN (SELECT employee_id, max(time_stamp) as last_action FROM ts_timesheet GROUP BY employee_id) b
                ON a.employee_id=b.employee_id
                LEFT JOIN employees e ON e.id=a.employee_id
                LEFT JOIN ts_activity c ON a.activity_id=c.id
                LEFT JOIN worktype_core cwt ON cwt.actionId=a.activity_id
                WHERE a.time_stamp=b.last_action
                AND e.status="1"
                GROUP BY a.employee_id
                ORDER BY sortOrder ASC, absact DESC, a.employee_id';
        return $this->execute($q);
    }

    public function getStatusToo()
    {
        // * route: payroll/status

        $employees = Employee::getActiveEmployees(['id', 'first_name', 'last_name', 'nickname']);

        $container = [];

        foreach ($employees as $k => $v) {
        }

        $status = $this->returnStatus();

        foreach ($status as $k => $v) {
            if ($v['actionId'] < 0) {
                $v['actionId'] = 1;
            } else if ($v['actionId'] > 10) {
                $v['actionId'] = 1;
            }
            $active = rand(0, 1);
            if ($active) {
                $length = rand(0, 100);
                $status[$k]['shiftLength'] = '<div class="payroll-status-length" style="width:' . $length . '%"></div>';
                $status[$k]['break'] = rand(0, 1) ? '<div class="payroll-break-status">
                <div class="payroll-break-length" style="width: ' . rand(0, 90) . '%"></div>
                </div>' : '';
                $status[$k]['dasharray_calc'] = 'calc(25 * 157.5/100) 157.5';
                $status[$k]['break_length_remaining'] = 15;
                $status[$k]['stroke'] = 'rgb(255, 223, 163)';
            } else {
                $status[$k]['shiftLength'] = '';
                $status[$k]['break'] = '';
                $status[$k]['actionId'] = 0;
                $status[$k]['stroke'] = 'white';
                $status[$k]['break_length_remaining'] = '';
            }
        }

        $this->_newData['status'] = $status;

        $this->render('status2.html', 'empty');
    }
    /**
     *
     */
    public function getStatus()
    {
        // * route: payroll/status

        $emps = Employee::getActiveEmployees(['first_name', 'nickname', 'id as employee_id']);

        foreach ($emps as $k => $v) {
            //
            extract($v);
            $actionId = Timesheet::getLastEmployeeActivity($employee_id)['activity_id'];
            if ($actionId < 0) {
                $actionId = 1;
            } else if ($actionId > 10) {
                $actionId = 1;
            }

            $emps[$k]['nickname'] = $nickname ?: $first_name;
            $emps[$k]['actionId'] = $actionId;
        }

        array_multisort(array_column($emps, 'actionId'), SORT_DESC, $emps);


        $this->render('status.html', ['status' => $emps], 'empty');
    }

    /**
     *
     */
    public function getOnBreak()
    {
        // * route: payroll/onbreak
        $employeesOnBreak = Timesheet::getOnBreak();

        $this->render('onbreak.html', ['employeesOnBreak' => $employeesOnBreak], 'empty');
    }

    /**
     *
     */
    public function getClockedIn()
    {
        $status = ['Clocked Out', 'Clocked In', 'Clocked Out', 'On Break', 'Clocked In', 'On Lunch', 'Clocked In'];
        // Temporarily index will pull all active employees that are clocked in
        // does not account for  being on lunch, ie activity_id '5'

        $status = $this->returnStatus();

        foreach ($status as $k => $v) {
            if ($v['actionId'] < 0) {
                $v['actionId'] = 1;
            } else if ($v['actionId'] > 10) {
                $v['actionId'] = 1;
            }
            $status[$k]['actionId'] = $v['actionId'];
        }

        $this->_newData['status'] = $status;
    }

    /**
     * * route: /payroll/record/reviewed
     *
     * @return void
     */
    public function putReviewed()
    {
        foreach ((array) $this->props->stamp_ids as $id) {
            \Proaction\Model\Timesheet::update(['id' => $id, 'activity_flag' => $this->props->state]);
            $this->_addReviewStatusComment($id, $this->props->state);
            // TODO - add reset for doublecheck status if switching alert status to 'unreveiwed'
        }
    }

    private function _addReviewStatusComment(int $timesheet_id, string $state)
    {
        $comment = "Changed reviewed status of Timestamp $timesheet_id to  $state";
        $shift_id = \Proaction\Model\Timesheet::where('id', $timesheet_id)->get('shift_id');
        return \Proaction\Model\TimesheetCommentChain::save(compact('timesheet_id', 'shift_id', 'comment'));
    }

    public function getTotalHoursByDate()
    {
        $date = $this->props->date ?: date('Y-m-d');
        $dept = $this->props->dept;
        echo 'testing';
        $x = RawShiftHours::where('date', $date)->get();
        Arr::pre($x);
        $employees = $this->_getAllEmployees($dept);
    }

    private function _getAllEmployees($dept = null)
    {
        if (is_null($dept)) {
            return EmployeeView::where('status', 1)->get();
        } else {
            return EmployeeView::where('department_id', $dept)->get();
        }
    }

    private function _getAllShiftsByDate($date)
    {
    }
}
