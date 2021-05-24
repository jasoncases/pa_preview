<?php

namespace Proaction\Domain\Timesheets\Resource;

use Proaction\Domain\Timesheets\Model\Timesheet;
use Proaction\Domain\Timesheets\Model\TimesheetCommentChain;
use Proaction\Domain\Users\Resource\UserFactory;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Status\Status;

class TimesheetAction
{

    protected $employeeId;

    /**
     * activity_id is a foreign key for the worktype_core table. Core
     * ids are:
     * 0    - clock out
     * 1    - clock in
     * 3/-3 - break in/out
     * 5/-5 - lunch in/out
     *
     * @var int
     */
    protected $activityId;

    public function __construct($employeeId, $activityId)
    {
        $this->employeeId = $employeeId;
        $this->activityId = $activityId;
    }

    /**
     * Perform the punch action
     *
     * @return void
     */
    public function punch()
    {
        try {
            $this->_createRecord();
            $this->_createCommentChain();
            return (new Status())->echo();
        } catch (\Exception $e) {

            die('Timesheet Action: ' . $e->getMessage());
            return (new Status())->error();
        }
    }

    /**
     * Mocks a punch
     *
     * @return void
     */
    public function mock()
    {
        return $this->_action()->debugTest($this->employeeId, $this->activityId);
    }
    /**
     * Call TimesheetActionFilter::punch() to actually do the logic of
     * inserting the record to the Timesheet table. ALl secondary
     * actions are handled in there and in Action
     *
     * @return void
     */
    private function _createRecord()
    {
        return $this->_action()->punch($this->employeeId, $this->activityId);
    }

    /**
     * Wrapped this class instance in a method to make it easier to swap
     * out in the future
     *
     * @return TimesheetActionFilter
     */
    private function _action()
    {
        return new TimesheetActionFilter();
    }

    /**
     * Document when a manual timestamp is created by a user other than
     * the one with the provided id
     *
     * @return TimesheetCommentChange
     */
    private function _createCommentChain()
    {
        if ($this->_timestampCreatedByAdministratorAction()) {
            $record = Timesheet::latest()->first();
            return TimesheetCommentChain::p_create([
                'timesheet_id' => $record->id,
                'shift_id' => $record->shift_id,
                'comment' => 'Manual timestamp created by administrator',
            ]);
        }
    }

    /**
     * Return true if the employeeIds conflict
     *
     * @return boolean
     */
    private function _timestampCreatedByAdministratorAction()
    {
        $user = UserFactory::create();
        return $this->employeeId != $user->get('employeeId');
    }
}
