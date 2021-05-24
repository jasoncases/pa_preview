<?php

namespace Proaction\System\Controller;

use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\Domain\Timesheets\Model\Shift;
use Proaction\Domain\Timesheets\Model\Timesheet;

use Proaction\Domain\Users\Model\Pin;
use Proaction\System\Views\GeneralView;

class TimeclockController extends BaseProactionController
{

    protected $_viewPath = 'timeclock/';

    private $_method;

    protected $_data = (object)[];

    // private $_class;

    const _TABLE = 'employees';

    public function __construct()
    {
    }

    public function index()
    {
        // * route: /template [GET]
        // $this->render('index.html');
        return view('Domain.TimeClock.index', GeneralView::add());

    }

    /**
     *  Timeclock Action
     *  /timeclock [POST]
     */
    public function store()
    {

        try {

            $encodedPin = $this->_data->secure;

            $pin = new Pin();
            $user = $pin->setPin($encodedPin)->getUserByPin();

            if (is_null($user)) {
                die(json_encode(['status' => 'error', 'msg' => 'No user found.']));
            }
            // have the employee
            $employee = $this->_getEmployeeByUserId($user['id']);

            if (is_null($employee)) {
                die(json_encode(['status' => 'error', 'msg' => 'Employee not found.']));
            }
            // get allowed actions
            $lastAction = $this->_getEmployeeTimesheetState($employee['id']);
            echo json_encode(array_merge(['status' => 'success'], $employee, $lastAction));
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        // * route: /template [POST]

        // echo __FUNCTION__;
        // print_r($data);

        // $this->render('user.index');
    }

    private function _getEmployeeByUserId($user_id)
    {
        return EmployeeView::where('user_id', $user_id)->get('first_name as firstName', 'id', 'last_name as lastName');
    }
    private function _getEmployeeTimesheetState($employee_id)
    {
        return Timesheet::where('employee_id', $employee_id)->last()->get('activity_id as currentStatus');
    }
    /**
     *  The page for creating new users
     */
    public function create()
    {
        // * route: /template/create
    }

    public function show($id)
    {
        // * route: /template/{id} [GET]
        $this->getActiveShifts($id);
    }

    public function update()
    {
        // * route: /template/{id} [PUT]
        // echo __CLASS__;
        // echo '<br />';
        // echo __FUNCTION__;
    }
    public function edit()
    {
        // * route: /template/{id}/edit

    }

    public function destroy()
    {
        // * route: /template/{id} [DELETE]

    }

    public function getActiveShifts()
    {
        echo json_encode(Shift::getActive(true));
    }
}
