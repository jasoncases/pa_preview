<?php

namespace Proaction\Domain\Timesheets\Controller;

use Illuminate\Http\Request;
use Proaction\Domain\Timesheets\Resource\TimesheetAction;
use Proaction\System\Controller\BaseProactionController;
use Proaction\System\Model\AccessLog\AccessLog;
use Proaction\System\Resource\Logger\Log;
use Proaction\System\Resource\Status\Status;

/**
 * Recieves and process employee Timesheet Action requests.
 *
 * * route: /timesheet_actions [POST]
 */
class TimesheetActionsController extends BaseProactionController
{
    // set logging to false, so we can log specific data when an action
    // is requested
    protected $logAccess = false;

    public function __invoke(Request $req)
    {
        try {
            // specifically log the controller AccessLog, so we can
            // capture the provided activity id.
            AccessLog::controllerLog(true, ['activity_id' => $req->input('activity_id')]);

            // process the requested Timesheet Action
            return (new TimesheetAction($req->input('employee_id'), $req->input('activity_id')))->punch();
        } catch (\Exception $e) {
            // log the error in the controller access log
            $req->merge(['error_message' => $e->getMessage()]);
            AccessLog::controllerLog(true, $req->all());
            // log the error in the actual logs
            Log::error($e->getMessage(), $req->all());
            // return the async error
            (new Status())->aux(['message' => $e->getMessage()])->error();
        }
    }
}
