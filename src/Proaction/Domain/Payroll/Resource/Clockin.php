<?php

namespace Proaction\Domain\Payroll\Resource;

use Proaction\System\Resource\Helpers\Arr;

class Clockin extends Action
{
    protected $filePath = 'View/payroll/actions/clockin.html';

    protected function _parse()
    {
        // echo '<pre>';
        // print_r($this->action);
        $this->_data['display_start_time'] = $this->_formatTime($this->action['time_stamp']);
        $this->_data['display_start_time'] = $this->_formatTime($this->action['time_stamp']);
        $this->_data = array_merge($this->_data, $this->action);
        // print_r($this->_data);
    }

    public function getDate()
    {
        return $this->_formatDate($this->action['time_stamp']);
    }

    public function setRelatedTo($clockOutId)
    {
        $this->_data['start_related_to'] = $clockOutId;
    }
    /**
     */
    protected function _setEditAuditData(array $audit)
    {
        $audit = current(Arr::retMulti($audit));
        $this->_editAuditData['display_time'] = $this->_formatTime($audit['time_stamp']);
        $this->_editAuditData['action_label'] = 'Clock In';
        $this->_editAuditData['diff'] = $this->_getDiffBetweenTimestamps($audit['time_stamp'], $this->action['time_stamp']);
        $this->_editAuditData['filename'] = 'editAuditStart';
    }

    protected function _getAudits()
    {
        $this->_audits = $this->_getAudit([$this->action['id']]);
    }

    protected function _setEditDisplayState()
    {
        $this->_data['edited'] = $this->_editConditionExists ? 'pr-edit-row-green' : '';
    }

    public function getId()
    {
        return $this->_data['id'];
    }
}
