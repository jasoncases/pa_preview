<?php

namespace Proaction\Domain\Payroll\Resource;

use Proaction\Domain\Display\DisplayVersionTwo;
use Proaction\Domain\Timesheets\Model\TimestampAudit;

use \DateTime;

class Action
{

    protected $filePath = '';
    protected $_data = [];
    protected $_audits = [];
    protected $_editAuditData = [];
    protected $_editConditionExists = false;

    public function __construct($action)
    {
        $this->action = $action;
    }
    public function init()
    {
        $this->_init();
    }
    protected function _duration()
    {
    }

    public function getUnixTs()
    {
        return $this->action['unix_ts'];
    }

    protected function _init()
    {
        $this->_parse();
        $this->_extendProps();
        $this->_getAudits();
        $this->_checkForAuditCondition();
    }

    protected function _extendProps()
    {
    }

    protected function _parse()
    {
    }

    protected function _formatTime($timestamp)
    {
        return $timestamp ? date('H:i:s', strtotime($timestamp)) : '';
    }

    protected function _formatDate($timestamp)
    {
        return $timestamp ? date('Y-m-d', strtotime($timestamp)) : '';
    }

    public function render()
    {
        if ($this->_editConditionExists) {
            $this->_renderEditAudit($this->_editAuditData['filename']);
            $this->_setEditDisplayState();
        }
        return $this->_render();
    }

    protected function _setEditDisplayState()
    {
    }

    protected function _render()
    {
        $display = new DisplayVersionTwo();
        $display->setLayout('empty');
        $display->setFilePath($this->filePath);
        $display->setData($this->_data);
        $display->render();
    }

    protected function _checkForAuditCondition()
    {
        if (!is_null($this->_audits)) {
            $this->_setEditAuditData($this->_audits);
            $this->_editConditionExists = true;
        }
    }

    protected function _renderEditAudit(string $filename)
    {
        $display = new DisplayVersionTwo();
        $display->setLayout('empty');
        $display->setFilePath('View/payroll/actions/' . $filename . '.html');
        $display->setData($this->_editAuditData);
        // $display->render();
    }

    protected function _getDiffBetweenTimestamps(string $newTime, string $oldTime)
    {
        $timeStamp1 = new DateTime($oldTime);
        $timeStamp2 = new DateTime($newTime);
        $diff = $timeStamp2->diff($timeStamp1);
        return $diff->format('%R%d:%H:%I:%S');
    }

    protected function _getAudit(array $stampIds)
    {
        return TimestampAudit::whereIn('timesheet_id', $stampIds)->latest('id')->get();
    }

    protected function _setEditAuditData(array $audit)
    {
    }
    protected function _getAudits()
    {
    }
    /**
     * start_action_id
     * shift_id *
     * action_label
     * employee_id *
     * start_time format(*)
     * start_date format(*)
     * display_start_time
     * end_time
     * end_date
     * end_action_id
     * end_action_id
     * display_end_time
     * duration
     *
     * ! - audits can be handled differently
     * end_audit
     * start_audit
     *
     * ! - state nodes
     * alert_state
     * location
     * manual_flag_state
     * comment_state
     *
     */
}
