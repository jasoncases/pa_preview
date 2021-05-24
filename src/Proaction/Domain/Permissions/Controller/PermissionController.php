<?php

namespace Proaction\Domain\Permissions\Controller;

use Illuminate\Support\Facades\Redirect;
use Proaction\Domain\Employees\Model\EmployeePermissions;
use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\Domain\Permissions\Model\AccessCore;
use Proaction\Domain\Permissions\Model\AccessDetail;
use Proaction\Domain\Permissions\Model\PermissionCategory;
use Proaction\Domain\Permissions\Model\PermissionLevels;
use Proaction\System\Controller\BaseProactionController;
use Proaction\System\Database\CDB;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Views\GeneralView;

/**
 * PermissionController handles user permission levels and access throughout the application
 *
 * * Relevant tables:
 * * employee_permissions: assign a permission level, via permission_id (permission_levels::id) to an employee, via employee_id (employees::id)
 * * permission_levels: defines permission level name (permission_label) and give it an id #
 * * permission_access_core: assign a permission_short_name, longform descriptino and other details to permission access
 * * permission_access_detail: correlate permisson_access_detail with specific permission_levels
 * *
 *
 * permissions are checked through \Controller\Controller:
 *
 * * $shortCode = 'allow_edit_permissions';
 * * $this->checkPermissionAccess($shortCode);
 *
 * where shortCode is defined as whatever relevant string
 *
 *
 * TODO - RESTRICT SUPERADMIN FROM REMOVING SELF FROM PERMISSIONS
 */
class PermissionController extends BaseProactionController
{
    protected $linkId = 10;
    protected $title = "Human Resources";

    /**
     * constructor method.
     *
     * @param string $method
     * @param string $data
     */
    public function __construct()
    {
    }

    private function _renderPermissionDelete($value)
    {
        return "<input type=\"hidden\" id=\"id\" value=\"$value\" />
                <button id=\"delete\" type=\"button\" class=\"btn btn-transparent btn-no-margin text-danger\">
                  <i class=\"fas fa-times-circle\"></i>
                </button>";
    }
    /**
     * Main view
     *
     * @return void {Render view: permissions.index}
     */
    public function index()
    {
        // get all permissions
        $permissions = PermissionLevels::all();

        foreach ($permissions as $k => $v) {
            //

            $permissions[$k]['delete'] = $v->mutable ? $this->_renderPermissionDelete($v->id) : '';
        }
        // submit action before the check, to log potential malicious activity


        // Perform permission check
        $shortCode = 'allow_edit_permissions';
        $this->checkPermissionAccess($shortCode);

        // loop through all permissions and get the # of assigned per category
        foreach ($permissions as $k => $v) {
            $permission_id = $v['id'];
            // Index added
            $permissions[$k]['assigned'] = EmployeePermissions::where(
                'permission_id',
                $permission_id
            )
                ->where('b.status', 1)
                ->leftJoin('employees', 'b', 'id', 'employee_id')
                ->get(CDB::raw('COUNT(*) as count'));
        }

        // save permissions to top-level _newData object
        $this->_newData['permission'] = $permissions;

        // $this->pre();
        // $this->render(
        //     'index.html',
        //     [
        //         'permission' => $permissions,
        //         'guestId' => PermissionLevels::where('permission_label', 'Guest')->get('id'),
        //     ],
        // );
        return view('Domain.Permissions.index', GeneralView::add([
            'permission' => $permissions,
            'guestId' => PermissionLevels::where('permission_label', 'Guest')->get('id'),
        ]));
    }

    /**
     * Store all details to permission_access_detail table
     *
     * @param array $accessArray - array of permission states
     * @return void
     */
    private function storeNewPermissionAccessDetail($accessArray)
    {
        // get the last id inserted into permission_levels
        $permission_id = PermissionLevels::lastInsertId();

        // loop through the supplied array
        foreach ($accessArray as $k => $value) {
            $value = $value == '' ? 0 : $value;

            // get the access_id from the short code (i.e., key of the supplied array)
            $access_id = $this->getAccessIdFromPermissionShortCode($k);

            // w/ the access_id, push the value and the permission_id (found above) to the permission_access_detail table
            $this->insertNewPermissionAccessDetail($permission_id, $access_id, $value);
        }
    }

    /**
     *
     *
     * @param int $permission_id
     * @param int $access_id
     * @param bool $value [0, 1]
     * @return void
     */
    private function insertNewPermissionAccessDetail($permission_id, $access_id, $value)
    {
        return AccessDetail::create(compact('access_id', 'permission_id', 'value'));
    }

    /**
     * Add new permission group and store permission_access_details
     *
     * @return void
     */
    public function store()
    {
        // $permission_id = ModelPermissionLevels::save(
        //     ['permission_label', $this->props->permission_label]
        // );
        try {
            AccessDetail::saveNewPermissionGroup((array)$this->props);
            $this->message('New Permission group [' . $this->props->permission_label . '] added');
        } catch (\Exception $e) {
            $this->message('Error saving permission group [' . $this->props->permission_label . ']', 'error');
        } finally {
            header("Location: /permissions");
        }
    }

    /**
     * Render the create view
     *
     * @return void {Render view: permission.create}
     */
    public function create()
    {
        //
        $this->render('create.html', 'create_top');

        $this->getPermissionQuestions();

        $this->render('create_lower.html', 'empty');
    }

    public function getPermissionQuestions()
    {

        $levels = PermissionCategory::all();
        $c = [];
        foreach ($levels as $lvl) {
            $c[] = [
                'permission_label' => $lvl['label'],
                'permissions' => $this->_getPermissionsByCategoryId($lvl['id']),
            ];
        }


        foreach ($c as $k => $v) {
            $this->render('accessqs.html', $v, 'empty');
        }
    }

    private function _getPermissionsByCategoryId($id)
    {

        $core = AccessCore::where('category_id', $id);
        if ($this->props->id) {
            $r = $core->where('b.permission_id', $this->props->id)
                ->leftJoin('permission_access_detail as b', 'b.access_id', 'a.id')
                ->get(
                    [
                        'a.id',
                        'a.permission_short_name',
                        'a.longform_text',
                        CDB::raw('IF(b.value =1, "checked", "") as checked'),
                    ]
                );
        } else {
            $r = $core->get(
                [
                    'id',
                    'permission_short_name',
                    'longform_text',
                    CDB::raw('" " as checked'),
                ]
            );
        }
        return $r;
    }

    /**
     * Redirect to root route
     */
    public function show()
    {
        //
        return new Redirect('/permissions');
    }

    /**
     *
     * @param int $permission_id
     * @return void
     */
    private function setDefaultValueForIncompletePermissionLevels($permission_id)
    {
        // get all permission levels
        $allPermissionAccess = $this->getAllPermissionAccess();

        // Based on all existing Permission Access Core levels, loop through and check if the user is missing a value
        // This will happen when Access Core levels are added, because the legacy user data doesn't automatically update
        // This could potentially be udpate, but this check will work for now.
        foreach ($allPermissionAccess as $k => $v) {
            extract($v);

            // get all info where access_id AND permission_id exist
            $result = AccessDetail::where('permission_id', $permission_id)
                ->where('access_id', $access_id)
                ->get();

            // if there is not a result, insert default value '0', so it can be updated
            if (empty($result)) {

                // insert default value
                $value = 0;
                return AccessDetail::save(compact('value', 'access_id', 'permission_id'));
            }
        }

        return null;
    }

    /**
     * Get all permission access ids
     *
     * @return array
     */
    private function getAllPermissionAccess()
    {
        return AccessCore::all('id as access_id', 'permission_short_name');
    }

    /**
     * Update permission_access_details
     *
     * @return void
     */
    public function update()
    {

        try {
            AccessDetail::updatePermissionGroup((array) $this->props);
            $this->message('Permission group [' . $this->props->permission_label . '] updated successfully');
            header("Location: /permissions/" . $this->props->id . "/edit");
        } catch (\Exception $e) {
            $this->message('Error updating user permissions', 'danger');
            header("Location: /permissions");
        }
    }
    public function postTest()
    {

        echo "postTest: ";
        Arr::pre($this->props);
    }

    private function _getAccessDetailId($access_id, $permission_id)
    {
        return AccessDetail::where('access_id', $access_id)
            ->where('permission_id', $permission_id)
            ->get('id');
    }
    /**
     * Check if a permission exists w/ a particular access_id/permission_id
     * ! REMEMBER TO ADD THE CORE DATA TO permission_access_core or you're gonna have a bad day
     *
     * @param int $access_id
     * @param int $permission_id
     * @return bool
     */
    private function permissionExists($access_id, $permission_id)
    {
        return AccessDetail::where('access_id', $access_id)
            ->where('permission_id', $permission_id)
            ->last()
            ->get(CDB::raw('COUNT(*) as count'));
    }

    /**
     * Get access id from permisison_short_name value
     *
     * @param string $permission_short_name
     * @return int access_id
     */
    private function getAccessIdFromPermissionShortCode($permission_short_name)
    {
        return AccessCore::where('permission_short_name', $permission_short_name)
            ->get('id');
    }

    /**
     * Get permission short name via the access_id value
     *
     * @param int $id
     * @return string permission_short_name
     */
    private function getPermissionShortNameByAccessId($id)
    {
        return AccessCore::where('id', $id)->get('permission_short_name');
    }

    /**
     * Gather permission access details by permission id
     *
     * @param int $permission_id
     */
    private function getPermissionAccessDetailsByPermissionId($permission_id)
    {
        $accessDetails = AccessDetail::where('permission_id', $permission_id)
            ->get(['access_id', 'value']);

        $container = [];

        foreach ($accessDetails as $k => $v) {
            extract($v);
            $permissionShortName = $this->getPermissionShortNameByAccessId($access_id);
            $container[$permissionShortName] = $value;
        }

        return $container;
    }

    /**
     * Edit an existing permission group
     *
     * @return void {RENDER VIEW: permissions.edit}
     */
    public function edit()
    {

        // submit action before the check, to log potential malicious activity


        // Permission Check
        $shortCode = "allow_edit_permissions";
        $this->checkPermissionAccess($shortCode);

        // get permission details, id for gathering current users and details, label for rendering in the view
        $result = PermissionLevels::where('id', $this->props->id)->get();

        // alias id to permission_id for the following query to get the employees w/ this permission_id
        $employees = EmployeeView::where('permission_id', $this->props->id)
            ->where('status', 1)
            ->oldest('first_name')
            ->get(
                [
                    'department_label as dept',
                    'id as employee_id',
                    'bar_color',
                    'id',
                    CDB::raw('CONCAT(first_name, " ", UPPER(LEFT(last_name, 1)), ".") as displayName'),
                ]
            );


        // Get permission access details, key => value and convert to top-level _newData props
        $permissionAccessDetails = $this->getPermissionAccessDetailsByPermissionId($this->props->id);
        foreach ($permissionAccessDetails as $k => $v) {
            $this->_newData[$k] = $v;
        }

        // assign values to the _newData object
        $this->_newData['permission_label'] = $result['permission_label'];
        $this->_newData['id'] = $result['id'];
        $this->_newData['employeesAssignedToCurrentPermissionGroup'] = $employees;

        // render the view
        $this->render('edit.html', $result, 'create_top');
        $this->getPermissionQuestions();
        $this->render('edit_lower.html', 'empty');
    }

    /**
     * Remove a permission group
     *
     * @return void
     */
    public function destroy()
    {
        //
        // submit action before the check, to log potential malicious activity


        // Permission Check
        $shortCode = "allow_edit_permissions";
        $this->checkPermissionAccess($shortCode);

        try {

            // Delete the permission group
            PermissionLevels::delete($this->props->id);

            // Delete access details AND employee permissions by the same permission_id
            $this->removePermissionAccessDetailsByPermissionId($this->props->id);
            $this->removeEmployeePermissionLevelsByPermissionId($this->props->id);

            // return the message and status
            $this->message('Permission level deleted successfully');
            $this->status->echo();
        } catch (\PDOException $e) {

            $this->message($e->getMessage(), 'error');
            $this->status->error();
        }
    }

    /**
     * Remove hanging details from table 'Permission_access_detail'
     *
     * Called when a permission group is deleted with PermissionController::destroy()
     *
     * @param int $permission_id
     * @return void
     */
    private function removePermissionAccessDetailsByPermissionId(int $id)
    {
        return AccessDetail::deleteWhere('permission_id', $id);
    }

    /**
     * Removes hanging employee permissions from table 'employee_permissions'
     *
     * Called when a permission group is deleetd with PermissionController::destroy()
     *
     * @param int $permission_id
     * @return void
     */
    private function removeEmployeePermissionLevelsByPermissionId(int $id)
    {
        return EmployeePermissions::deleteWhere('permission_id', $id);
    }
}
