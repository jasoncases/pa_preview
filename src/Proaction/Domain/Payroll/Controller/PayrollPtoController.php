<?php

namespace Proaction\Domain\Payroll\Controller;

use Exception\PTOException;
use Proaction\Domain\Attendace\Model\Pto;
use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Payroll\Model\PayrollComplete;
use Proaction\Domain\Payroll\Model\PTORequest;
use Proaction\System\Controller\BaseProactionController;
use Proaction\System\Database\CDB;
use Proaction\System\Resource\Helpers\Arr;

class PayrollPtoController extends BaseProactionController
{
    private $_method;
    protected $_viewPath = 'payroll/pto/';
    protected $_layout = 'empty';
    protected $_data = [];

    const _TABLE = 'schedule_core';

    public function __construct()
    {
    }

    public function index()
    {

        $routeCode = "is_admin";
        if ($this->routePermissionAccess($routeCode)) {

            // admin can see all requests for approval or choose to enter their own
            // TODO - for now reroute admins as well until we get a readout of reports view
            // get all request for output
            $date = isset($this->_data['date']) ? $this->_data['date'] : date('Y-m-d');

            $month = date('F', strtotime($date));
            // Need to get requests for the week
            $pendingRequests = $this->getMonthlyPTORequestsByStatusAndDate('pending', $date);
            $pendingRequests = $this->completeFormattingOfRequestsObject($pendingRequests);

            //
            $approvedRequests = $this->getMonthlyPTORequestsByStatusAndDate('approved', $date);
            $approvedRequests = $this->completeFormattingOfRequestsObject($approvedRequests);

            //
            $deniedRequests = $this->getMonthlyPTORequestsByStatusAndDate('denied', $date);
            $deniedRequests = $this->completeFormattingOfRequestsObject($deniedRequests);

            //
            $totalPto = $this->getTotalPtoCount();

            $totalRequests = $this->getTotalRequestCount();

            $this->render('index.html', [
                'nickname' => Employee::find($this->id, ['first_name']),
                compact('pendingRequests'),
                compact('approvedRequests'),
                compact('deniedRequests'),
                'selected_date' => $date,
                'employee_id' => $this->id,
                'month' => $month,
                'admin_request_badge' => $this->getBadgeStatus($this->getTotalRequestCount()),
                'admin_pto_badge' => $this->getBadgeStatus($this->getTotalPtoCount()),
            ]);
        } else {

            // render non-admin, self-edit page

            header('Location: /pto/' . $this->id . '/edit');
        }
    }

    private function getBadgeStatus($count)
    {
        return $count > 0 ? 'badge-alert' : '';
    }
    private function getTotalPtoCount()
    {
        return count($this->ptoRequests->getByColumnValue(['status' => 'pending'], ['id']));
    }
    private function getTotalRequestCount()
    {
    }
    public function show()
    {

        // Permission Check, non routing - returns a boolean
        $routeCode = "is_admin";
        if ($this->routePermissionAccess($routeCode)) {
        } else {
            die('this page is only available to administrators.');
        }
    }
    private function getEmployeePTORequestsByYear($employee_id, $year)
    {
        return PTORequest::where('employee_id', $employee_id)
            ->andWhere('YEAR(date)', $year)
            ->latest('date')
            ->get();
    }
    private function getUsedEmployeePTORequestsByYear($employee_id, $year)
    {
        return PTORequest::where('employee_id', $employee_id)
            ->andWhere('YEAR(date)', $year)
            ->andWhere('status', '!=', 'denied')
            ->latest('date')
            ->get();
    }
    public function edit()
    {

        $year = isset($this->_data['year']) ? $this->_data['year'] : date('Y');

        $allRequests = $this->getEmployeePTORequestsByYear($this->props->id, $year);
        $allRequests = $this->formatRequests($allRequests);

        $ptoEarned = $this->getEarnedPtoByEmployeeId($this->props->id);
        $ptoUsed = $this->getUsedEmployeePTORequestsByYear($this->props->id, $year);
        $ptoRemaining = $ptoEarned - $ptoUsed;
        // gatekeep some values.

        $totalHours = $this->getTotalHoursForAccrualByEmployeeId($this->props->id, $year);
        $daysAccrued = $this->getAccrualStatusByTotalHours($totalHours);
        //
        $this->render('edit.html', [
            'nickname' => Employee::find($this->id, ['first_name']),
            compact('allRequests'),
            'totalHours' => $totalHours,
            'daysAccrued' => $daysAccrued,
            'pto_earned' => $ptoEarned,
            'pto_used' => $ptoUsed,
            'pto_remaining' => $ptoRemaining,
            'employee_id' => $this->props->id,
            'year' => $year,
            'prevYear' => $year - 1,
            'nextYear' => $year + 1,
            'admin_request_badge' => $this->getBadgeStatus($this->getTotalRequestCount()),
            'admin_pto_badge' => $this->getBadgeStatus($this->getTotalPtoCount()),
        ]);
    }
    /**
     *
     */
    private function completeFormattingOfRequestsObject($array)
    {
        // $mapTime = ['[AM Only]', '[PM Only]', '&nbsp;'];

        foreach ($array as $k => $v) {
            extract($v);
            $emp = Employee::find($employee_id, ['first_name', 'last_name']);
            $array[$k]['employee_name'] = implode(' ', $emp);
            // $array[$k]['day_part'] = $mapTime[$time];
            $array[$k]['comment_output'] = is_null($comments_employee) ? '' : '<i class="fal fa-comment text-grey"></i> <span>' . $comments_employee . '</span>';
        }

        $date = array_column($array, 'date');

        array_multisort($date, SORT_DESC, $array);

        return $array;
    }
    private function getEarnedPtoByEmployeeId($employee_id)
    {
        $num = Pto::getEarnedPto($employee_id);
        return !is_null($num) ? $num : 0;
    }

    private function getUsedPtoByEmployeeId($employee_id)
    {

        $mode = 'CalendarYear';
        // toggle calendarYear vs DateOfEmployment

        if ($mode == 'CalendarYear') {
            $to = date('Y-12-31 23:59:59');
            $from = date('Y-01-01 00:00:00');
        } else {
            //

        }

        return PTORequest::whereBetween('date', [$from, $to])
            ->where('employee_id', $employee_id)
            ->where('statis', '!=', 'denied')
            ->first(CDB::raw('COUNT(*) as count'))->count;
    }

    private function formatRequests($requestObj)
    {
        //

        foreach ($requestObj as $k => $v) {
            //
            extract($v);
            if ($status == 'denied') {
                $requestObj[$k]['cancel'] = '';
            } else {
                $requestObj[$k]['cancel'] = strtotime($date) < time() ? '' : $this->cancelButton($id);
            }
        }

        return $requestObj;
    }

    public function getAllPtoAdmin()
    {
        //
        $container = [];

        $result = $this->getAllRequests();

        foreach ($result as $k => $v) {
            extract($v);
            $adjStatus = $status == 'pending' ? 'action' : 'approved';
            if (isset($container[$date])) {
                $container[$date]['status'] = $container[$date]['status'] != 'action' ? $adjStatus : 'action';
            } else {
                $container[$date]['date'] = $date;
                $container[$date]['status'] = $adjStatus;
            }
        }
        $this->status->aux(array_values($container))->echo();
    }

    private function getAllRequests()
    {
        $from = date('Y-m-01', strtotime('-2 months'));
        return PTORequest::where('date', '>=', $from)->latest('date')->get();
    }

    private function cancelButton($id)
    {
        return '<button
                  id="ui:requestStatus"
                  type="button"
                  class="btn btn-transparent text-dark"
                  style="padding:0;margin: auto 0;"
                  data-id="' . $id . '">
                  <span class="text-secondary" style="margin-right:8px;">Cancel PTO</span>
                  <i class="fas fa-times-circle text-danger"></i>
               </button>';
    }

    public function store()
    {
        //

        // phpCore try catch block
        try {

            /**
             * INCOMING:
             * employee_id, date
             */

            $incomingData = json_decode($this->_data['data'], true);

            // code block ....
            $earned = $this->getEarnedPtoByEmployeeId($this->id);
            $used = $this->getUsedPtoByEmployeeId($this->id);

            if ($used >= $earned) {
                throw new \Exception\PTOException('No remaining PTO days. Please see a manager for more information.');
            }

            if (!PTORequest::save(compact('employee_id', 'date', 'comments_employee'))) {
                throw new \Exception\DatabaseOperationException('Failed to save PTO data.');
            }

            $this->status->echo();
            //$this->message('');
        } catch (PTOException $e) {
            $this->status->error();
            $this->message($e->getMessage(), 'error');
        }
    }
    /**
     *
     */
    private function getMonthlyPTORequestsByStatusAndDate($status, $from)
    {

        // $from = $this->startOfMonth($from);
        $month = date('m', strtotime($from));
        $year = date('Y', strtotime($from));

        return PTORequest::where('YEAR(date)', $year)
            ->andWhere('MONTH(date)', $month)
            ->andWhere('status', $status)
            ->latest('date')
            ->get();
        // $from = date('Y-m-01', strtotime('-2 months'));
    }

    public function create()
    {
    }
    public function read()
    {
    }
    public function destroy()
    {
        // phpCore try catch block ....
        try {
            // code block ....
            if (!$this->ptoRequests->delete($this->props->id)) {
                throw new \Exception\DatabaseOperationException('Unable to delete item.');
            }

            // update earned, used and remaining, send back to the view via aux()
            $earned = $this->getEarnedPtoByEmployeeId($this->id);
            $used = $this->getUsedPtoByEmployeeId($this->id);
            $remaining = $earned - $used;

            $this->status->aux(compact('earned', 'used', 'remaining'))->echo();
            //$this->message('');
        } catch (\Exception $e) {
            $this->status->error();
            $this->message($e->getMessage(), 'error');
        }
    }
    public function privateKeywords()
    {
    }
    public function update()
    {
        //
        // $this->pre();
        $incomingData = json_decode($this->_data['data'], true);
        // phpCore try catch block
        try {
            // code block ....
            if (!$this->ptoRequests->update($incomingData)) {
                throw new \Exception\DatabaseOperationException('Update request status failed');
            }

            sleep(1);
            $this->getAllPtoAdmin();
            //$this->message('');
        } catch (\Exception $e) {
            $this->status->error();
            $this->message($e->getMessage(), 'error');
        }
    }

    public function getTest()
    {
        echo 'this is total bull and should not work';
    }

    /**
     * Defines accrual method based on number of hours in Client Mutable Accrual Method. Default is 102
     */
    private function getAccrualStatusByTotalHours($totalHours)
    {

        return floor($totalHours / 192);
    }

    /**
     *
     * @param int $employee_id
     * @param string $year
     *
     * @return string hours
     */
    private function getTotalHoursForAccrualByEmployeeId($employee_id, $year)
    {
        return number_format(
            array_sum(
                Arr::flatten(
                    PayrollComplete::where('employee_id', $employee_id)
                        ->andWhere('YEAR(created_at)', $year)
                        ->get('_paid')
                )
            ) / 3600,
            2,
            '.',
            ''
        );
    }

    public function getShowAll()
    {
        $title = 'Employee PTO Accrual';
        // current year if not set
        $year = isset($this->_data['year']) ? $this->_data['year'] : date('Y');

        // named container
        $allPTO = [];

        // all active employees $status = 1
        $allEmployees = Employee::getActiveEmployees(
            [
                'id',
                'first_name',
                'last_name',
                'email'
            ]
        );

        //
        foreach ($allEmployees as $k => $v) {
            extract($v);
            $totalHours = $this->getTotalHoursForAccrualByEmployeeId($id, $year);
            $accruedDays = $this->getAccrualStatusByTotalHours($totalHours);
            $usedDays = $this->getUsedEmployeePTORequestsByYear($id, $year);
            $allPTO[] = compact('id', 'first_name', 'last_name', 'totalHours', 'accruedDays', 'usedDays');
        }

        $this->render('showall.html', [compact('allPTO'), 'year' => $year, 'nextYear' => $year + 1, 'prevYear' => $year - 1, 'title' => $title]);
    }
}
