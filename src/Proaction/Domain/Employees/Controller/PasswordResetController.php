<?php

namespace Proaction\Domain\Employees\Controller;

use Illuminate\Auth\Events\PasswordReset;
use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\Domain\Permissions\Resource\PermissionCheck;
use Proaction\Domain\Users\Service\UpdateUserPassword;
use Proaction\System\Controller\BaseProactionController;

class PasswordResetController extends BaseProactionController
{

    protected $linkId = 0;
    protected $_viewPath = 'template/';
    public function index()
    {
    }

    public function store()
    {
        try {
            $email = $this->props->email;
            $pass = base64_decode(base64_decode($this->props->pass));
            $employee = EmployeeView::where('email', $email)->limit(1)->get();
            $updater = new UpdateUserPassword($employee);
            $updater->update($pass);
            $this->status->echo();
        } catch (\Exception $e) {
            $this->status->error();
        }
    }

    public function create()
    {
    }

    public function show()
    {
    }

    /**
     * Using this method to update the users password and send a new 
     * temporary password to the user's current email address
     *
     * @return void
     */
    public function update()
    {
        PermissionCheck::auth('is_admin');
        PermissionCheck::auth('allow_edit_employees');

        $pr = new PasswordReset($this->props->id);
        $pr->process();

        $this->status->echo();
    }

    public function edit()
    {
    }

    public function destroy()
    {
    }
}
