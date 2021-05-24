<?php

namespace Proaction\Domain\Payroll\Resource;

use Proaction\System\Resource\Helpers\Arr;

class Clockout extends Action
{

    protected $filePath = 'View/payroll/actions/clockout.html';

    protected function _parse()
    {
        $this->_data['display_end_time'] = $this->_formatTime($this->action['time_stamp']);
        $this->_data['duration'] = $this->_duration();
        $this->_data['editable'] = $this->action['state'] ? 'data-editable' : '';
        $this->_data['hover'] = $this->action['state'] ? 'pr-hover' : '';
        $this->_data['id'] = $this->action['id'];
        $this->_data['next_day'] = $this->_getNextDayText();
        $this->_data = array_merge($this->_data, $this->action);
        // echo ' this is the clockout class';
    }

    private function _getNextDayText()
    {
        if (is_null($this->action['time_stamp']) || $this->action['time_stamp'] == '') {
            return '';
        }
        return $this->_isNextDay() ? '(next day)' : '';
    }

    private function _isNextDay()
    {

        return date('w', strtotime($this->action['time_stamp'])) > $this->Shift->getStartDow();
    }

    protected function _duration() 
    {
        $start = $this->Shift->getClockInUnixTs();
        return number_format(round(($this->action['unix_ts'] - $start) / 3600, 2), 2, '.', '');
    }

    public function getDuration()
    {
        return $this->_data['duration'];
    }

    protected function _setEditAuditData(array $audit)
    {
        $audit = current(Arr::retMulti($audit));
        $this->_editAuditData['display_time'] = $this->_formatTime($audit['time_stamp']);
        $this->_editAuditData['action_label'] = 'Clock Out';
        $this->_editAuditData['diff'] = $this->_getDiffBetweenTimestamps($audit['time_stamp'], $this->action['time_stamp']);
        $this->_editAuditData['filename'] = 'editAuditEnd';
    }

    protected function _getAudits()
    {
        $this->_audits = $this->_getAudit([$this->action['id']]);
    }

    protected function _setEditDisplayState()
    {
        $this->_data['edited'] = $this->_editConditionExists ? 'pr-edit-row-green' : '';
        $this->_data['edited_cell'] = $this->_editConditionExists ? 'pr-edit-cell-green' : '';
    }

    private function _getRelatedTo()
    {
        $this->_data['end_related_to'] = $this->Shift->getClockInId();
    }

    private function _setClockInRelatedTo()
    {
        $this->Shift->setClockInRelatedTo($this->_data['id']);
    }

    protected function _extendProps()
    {
        $this->_getRelatedTo();
        $this->_setClockInRelatedTo();
    }
}
