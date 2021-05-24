<?php

namespace Proaction\Domain\Employees\Controller;

use Illuminate\Http\Request;
use Proaction\Domain\Employees\Model\Department;
use Proaction\Domain\Employees\Model\Employee;
use Proaction\Domain\Employees\Model\PendingEmployee;
use Proaction\Domain\Employees\Model\StatusTypes;
use Proaction\Domain\Employees\Resource\PendingBuilder;
use Proaction\Domain\Employees\Service\CreateNewEmployee;
use Proaction\Domain\Employees\ViewBuilders\PendingView;
use Proaction\Domain\Permissions\Model\PermissionLevels;
use Proaction\Domain\Permissions\Resource\PermissionCheck;
use Proaction\Domain\Users\Model\Pin;
use Proaction\System\Controller\BaseProactionController;
use Proaction\System\Database\CDB;
use Proaction\System\Resource\Cache\Cache;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Status\FlashAlert;
use Proaction\System\Views\GeneralView;

/**
 * Pending Employee Creation
 *
 * We're splitting employee creation off from the main employee control-
 * ler because there are multiple stages to creation and things were ge-
 * tting too jumbled in the original employee controller. Once employees
 * are created, they are edited/updated through the EmployeeController,
 * until then they are in the domain of "pending"
 *
 * Creation comes in 4 stages
 * 1.) Personal Information - before this form is complete, the system
 *      is completely ignorant of any of the employee information. Once
 *      the form is submitted, they are an incomplete record in the
 *      `pending_employee_creation` table.
 * 2.) Employment Information - All client employment relational info,
 *      i.e., employment status, department, date of hire, etc
 * 3.) Proaction Information - User permissions, pin code
 * 4.) Review - Lists out, report-style all pending employee information
 *      after `finalize` process runs to create user, employee, meta emp
 */
class PendingEmployeeController extends BaseProactionController
{

    protected $linkId = 10;
    protected $_viewPath = 'pending_emp/';
    protected $title = "Human Resources";
    protected $logAccess = true;

    private $dynamicRoutes = [
        'create',
        'employment',
        'other',
        'review',
    ];


    public function index()
    {
        return redirect('/pending_employees/create');
    }

    public function store(Request $req)
    {
        try {
            $builder = new PendingBuilder();
            $builder->create($req->all());
            return redirect('/pending_employees/' . $builder->employee->id . '/employment');
        } catch (\Exception $e) {
            new FlashAlert($e->getMessage(), 'danger');
            return redirect('/pending_employees/create');
        }
    }

    public function create()
    {
        return view('Domain.Employees.Pending.personal', PendingView::add());
    }

    public function show($id)
    {
        $builder = new PendingBuilder();
        $builder->get($id);
        return view('Domain.Employees.Pending.personal', GeneralView::add(
            [
                'employee' => $builder->employee,
                'progress' => $builder->progress,
            ]
        ));
    }

    public function update(Request $req)
    {
        try {
            $builder = new PendingBuilder();
            $builder->update($req->all());
            $route = $this->dynamicRoutes[$builder->progress];
            // determine next page by Pending creation progress
            return redirect("/pending_employees/" . $builder->employee->id . "/$route");
        } catch (\Exception $e) {
            new FlashAlert($e->getMessage(), 'danger');
            die($e->getMessage());
        }
    }

    public function employment($id)
    {
        $builder = new PendingBuilder();
        $builder->get($id);
        return view('Domain.Employees.Pending.employment', GeneralView::add([
            'employee' => $builder->employee,
            'progress' => $builder->progress,
            'employmentTypes' => StatusTypes::oldest('id')->get(),
            'departments' => Department::oldest('department_label')->get(),
        ]));
    }

    public function other($id)
    {
        $builder = new PendingBuilder();
        $builder->get($id);
        return view('Domain.Employees.Pending.other', GeneralView::add([
            'employee' => $builder->employee,
            'progress' => $builder->progress,
            'permissions' => PermissionLevels::all(), // Department::all(),
        ]));
    }

    public function review($id)
    {
        $builder = new PendingBuilder();
        $builder->review($id);
        return view('Domain.Employees.Pending.review', GeneralView::add([
            'employee' => $builder->employee,
        ]));
    }

    public function destroy($id)
    {
        try {
            PendingEmployee::destroy($id);
            new FlashAlert('Pending employee record successfully removed');
            return redirect('/employees');
        } catch (\Exception $e) {
            new FlashAlert($e->getMessage(), 'danger');
            return redirect('/pending_employees/' . $id . '/review');
        }
    }

    /**
     * Create the concrete user/employee records.
     *
     * @param int $id
     * @return void
     */
    public function commit($id)
    {
        try {

            // create all new employee records
            if (!CreateNewEmployee::create($id)) {
                throw new \Exception('Error creating employee');
            }

            // set user output
            new FlashAlert('New employee entered successfully - Login credentials have been emailed to the new user');

            // after new employee creation, bust and reload the cache,
            // otherwise, it'll take 15 minutes for the cache to reset
            // and include the new employee account. User will be able
            // to login immediately, but without a cache refresh, they
            // won't be able to clock in
            Cache::bustAndReloadCache('usersCache');

            // destroy the pending employee record.
            PendingEmployee::destroy($id);
        } catch (\Exception $e) {

            // set failure flash alert
            new FlashAlert('Error saving new employee', 'danger');
        } finally {

            // send user back to employees page
            return redirect('/employees');
        }
    }
}
