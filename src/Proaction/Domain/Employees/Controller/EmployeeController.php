<?php

namespace Proaction\Domain\Employees\Controller;

use Illuminate\Support\Facades\Redirect;
use Proaction\Domain\Employees\Model\Department;
use Proaction\Domain\Employees\Model\EmployeePermissions;
use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\Domain\Employees\Model\PendingEmployee;
use Proaction\Domain\Employees\Model\StatusTypes;
use Proaction\Domain\Employees\Service\UpdateEmployment;
use Proaction\Domain\Employees\Service\UpdateOther;
use Proaction\Domain\Permissions\Model\PermissionLevels;
use Proaction\Domain\Permissions\Resource\PermissionCheck;
use Proaction\Domain\Users\Model\UserSetting;
use Proaction\Service\Employee\ShowEmployment;
use Proaction\System\Controller\BaseProactionController;
use Proaction\System\Views\GeneralView;

class EmployeeController extends BaseProactionController
{
    protected $linkId = 10;
    protected $_viewPath = 'employee/';
    protected $title = "Human Resources";
    protected $logAccess = true;

    public function index()
    {
        $active = EmployeeView::getActiveEmployees(['first_name', 'last_name', 'id'], 'last_name');
        $pending = PendingEmployee::all();
        return view('Domain.Employees.index', GeneralView::add([
            'active' => $active,
            'pending' => $pending,
            'hasPending' => boolval($pending),
        ]));
    }

    public function show()
    {
        PermissionCheck::validate('allow_view_employees');
        $this->render(
            'show_personal.html',
            [
                'employee' => EmployeeView::find($this->props->id)
            ]
        );
    }

    public function getShowEmployment()
    {
        PermissionCheck::validate('allow_view_employees');
        $this->render(
            'show_employment.html',
            [
                'employee' => ShowEmployment::get($this->props->id),
                'departments' => Department::all(),
                'employmentTypes' => StatusTypes::all(),
            ]
        );
    }

    public function getShowOther()
    {
        PermissionCheck::validate('allow_view_employees');
        $this->render(
            'show_other.html',
            [
                'permission_id' => EmployeePermissions::where('employee_id', $this->props->id)->get('permission_id'),
                'allow_remote' => UserSetting::get('allow_remote_timesheet_action', $this->props->id),
                'permissions' => PermissionLevels::all(),
                'employee' => EmployeeView::find($this->props->id, ['first_name', 'last_name']),
            ]
        );
    }

    public function update()
    {
        // Permission checks are outside the try catch because we want
        // them caught by the index.php catch statements
        PermissionCheck::validate('allow_edit_employees', '/employee/' . $this->props->id);
        try {
            $update = new UpdateEmployment((array) $this->props);
            $update->commit();
            $this->message('Employee data has been updated', 'success');
        } catch (\Exception $e) {
            $this->message($e->getMessage(), 'danger');
        } finally {
            $this->show();
        }
    }

    public function putUpdateEmployment()
    {
        PermissionCheck::validate('allow_edit_employees', '/employee/' . $this->props->id . '/employment');
        try {
            $update = new UpdateEmployment((array) $this->props);
            $update->commit();
            $this->message('Employee data has been updated', 'success');
        } catch (\Exception $e) {
            $this->message($e->getMessage(), 'danger');
        } finally {
            return new Redirect('/employee/' . $this->props->id . '/employment');
        }
    }

    public function putUpdateOther()
    {
        PermissionCheck::validate('allow_edit_employees', '/employee/' . $this->props->id . '/other');
        try {
            $update = new UpdateOther((array) $this->props);
            $update->commit();
            $this->message('Employee data has been updated', 'success');
        } catch (\Exception $e) {
            $this->message($e->getMessage(), 'danger');
        } finally {
            return new Redirect('/employee/' . $this->props->id . '/other');
        }
    }
}
