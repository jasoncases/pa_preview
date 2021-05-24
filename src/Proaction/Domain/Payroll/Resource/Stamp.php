<?php

namespace Proaction\Domain\Payroll\Resource;

use Proaction\Domain\Worktypes\Model\Worktype;
use Proaction\System\Resource\Helpers\Arr;

class Stamp extends Action
{

    protected $start;
    protected $end;

    protected $filePath = 'View/payroll/actions/stamp.html';

    protected function _parse()
    {
        // echo ' this is a stamp class';
        $this->start = array_shift($this->action);
        $this->end = end($this->action);
        $this->_setDataValues();
    }

    private function _setDataValues()
    {
        $this->_setIds();
        $this->_setDisplays();
        $this->_setEditableState();
        $this->_setHoverState();
    }

    private function _setIds()
    {
        $this->_data['start_id'] = $this->start['state'] ? $this->start['id'] : '';
        $this->_data['end_id'] = $this->end['state'] ? $this->end['id'] : '';
    }

    private function _setDisplays()
    {
        $this->_data['display_start_time'] = $this->_formatTime($this->start['time_stamp']);
        $this->_data['display_end_time'] = $this->_formatTime($this->end['time_stamp']);
        $this->_data['duration'] = $this->_getDuration();
        $this->_data['action_label'] = $this->_getActionLabel();
    }

    private function _setEditableState()
    {
        // we assume it's editable on hover because if its not, it should be set to ''
        $this->_data['open_editable'] = $this->start['state'] ? 'data-editable' : '';
        $this->_data['close_editable'] = $this->end['state'] ? 'data-editable' : '';
    }

    private function _setHoverState()
    {
        $this->_data['open_hover'] = $this->start['state'] ? 'pr-hover' : '';
        $this->_data['close_hover'] = $this->end['state'] ? 'pr-hover' : '';
    }

    public function getModifiableDurations()
    {
        return $this->start['activity_id'] != 5 ? 0 : -$this->_getDuration();
    }
    private function _getDuration()
    {
        $start = $this->start['unix_ts'];
        $end = $this->end['unix_ts'];
        $duration = round(($end - $start) / 3600, 2);
        return number_format($duration, 2, '.', '');
    }

    private function _getActionLabel()
    {
        $label = Worktype::where('actionId', $this->start['activity_id'])->get('text');
        $label = preg_replace('/(End|Start)\s/', '', $label);
        return $label;
    }

    protected function _setEditAuditData(array $audit)
    {

        $start = $this->_filterAudit($this->start['id']);
        $end = $this->_filterAudit($this->end['id']);

        $filename = $this->_getEditAuditFilename($start, $end);
        $this->_editAuditData['filename'] = $filename;

        $this->_setEditAuditDataByFilename($filename, $start, $end);
    }

    private function _setEditAuditDataByFilename(string $filename, $start, $end)
    {
        if ($filename == 'editAuditBoth') {
            $this->_setEditAuditDataBoth($start, $end);
        } else {
            $this->_setEditAuditDataSingle($start, $end);
        }
    }

    private function _setEditAuditDataBoth($start, $end)
    {
        $this->_editAuditData['action_label'] = $this->_data['action_label'];
        $this->_editAuditData['display_start_time'] = $start ? $this->_formatTime($start['time_stamp']) : '';
        $this->_editAuditData['display_end_time'] = $end ? $this->_formatTime($end['time_stamp']) : '';
        $this->_editAuditData['start_diff'] = $start ? $this->_getDiffBetweenTimestamps($start['time_stamp'], $this->start['time_stamp']) : '';
        $this->_editAuditData['end_diff'] = $end ? $this->_getDiffBetweenTimestamps($end['time_stamp'], $this->end['time_stamp']) : '';
    }

    private function _setEditAuditDataSingle($start, $end)
    {
        $data = $start ? $start : $end;
        $target = $this->start['id'] == $data['timesheet_id'] ? $this->start : $this->end;
        $this->_editAuditData['action_label'] = $this->_data['action_label'];
        $this->_editAuditData['display_time'] = $this->_formatTime($data['time_stamp']);
        $this->_editAuditData['diff'] = $this->_getDiffBetweenTimestamps($data['time_stamp'], $target['time_stamp']);
    }
    private function _getEditAuditFilename($start, $end)
    {
        if (!$start && $end) {
            return 'editAuditEnd';
        } else if ($start && $end) {
            return 'editAuditBoth';
        } else if ($start && !$end) {
            return 'editAuditStart';
        }
        return false;
    }

    protected function _getAudits()
    {
        $this->_audits = $this->_getAudit([$this->start['id'], $this->end['id']]);
        if (!is_null($this->_audits)) {
            $this->_audits = Arr::retMulti($this->_audits);
        }
    }

    protected function _filterAudit(int $id)
    {
        return current(array_filter($this->_audits, function ($v, $k) use ($id) {
            return $v['timesheet_id'] == $id;
        }, ARRAY_FILTER_USE_BOTH));
    }

    protected function _setEditDisplayState()
    {
        $this->_data['edit'] = $this->_editConditionExists ? 'pr-edit-row-green' : '';
        $file = $this->_editAuditData['filename'];
        $this->_data['open_edited'] = $this->_handleEditedDataState($file, 'open');
        $this->_data['close_edited'] = $this->_handleEditedDataState($file, 'close');
    }

    private function _handleEditedDataState(string $filename, string $which)
    {
        if ($which == 'open') {
            return $filename == 'editAuditEnd' ? '' : 'pr-highlight-green';
        } else {
            return $filename == 'editAuditStart' ? '' : 'pr-highlight-green';
        }
    }
}
