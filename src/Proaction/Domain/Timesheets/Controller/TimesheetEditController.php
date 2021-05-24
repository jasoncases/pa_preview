<?php

namespace Proaction\Domain\Timesheets\Controller;

use Illuminate\Http\Request;
use Proaction\Domain\Permissions\Resource\PermissionCheck;
use Proaction\Domain\Timesheets\Resource\TimestampUpdater;
use Proaction\System\Controller\BaseProactionController;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Status\FlashAlert;
use Proaction\System\Resource\Status\Status;

/**
 * Edit timesheet records
 *
 *
 */
class TimesheetEditController extends BaseProactionController
{
    /**
     * Undocumented function
     *
     * * route: /timestamp_edit/{id} [PUT]
     *
     * @param Request $req
     * @return void
     */
    public function update($id, Request $req)
    {
        try {
            PermissionCheck::validate("allow_edit_payroll");
            $updater = new TimestampUpdater($id, $req->all());
            $updater->process();
        } catch (\Exception $e) {
            new FlashAlert($e->getMessage(), 'danger');
        } finally {
            return redirect("/payroll_edit/" . $req->input('shift_id'));
        }
    }
}
