<?php

namespace Proaction\Domain\Payroll\Service;

use Exception\PayrollShiftEmptyRecordException;
use Exception\PayrollShiftImpossibleRecordException;
use Exception\PayrollShiftIncorrectRecordNumberException;
use Exception\PayrollShiftMissingPunchException;
use Exception\PayrollShiftTooManyEmployeesException;
use Exception\PayrollShiftTooManyRecordsException;
use Exception\PayrollShiftTooManyShiftsException;
use Proaction\System\Resource\Helpers\Arr;

class ShiftSegmentValidator
{
    private $maxNumberOfRecords = 8;
    private $maxNumberOfLunches = 1;
    private $maxNumberOfBreaks = 2;

    private $segmentCalculator;

    public static function validate($timestamps, $segmentCalculator)
    {
        return (new static)->_validate($timestamps, $segmentCalculator);
    }

    private function _validate($timestamps, $segmentCalculator)
    {
        $this->segmentCalculator = $segmentCalculator;

        $this->_hasZeroRecords($timestamps);
        $this->_hasTooManyRecords($timestamps);
        $this->_hasTooManyEmployeeIds($timestamps);
        $this->_hasIncorrectNumberOfRecords($timestamps);
        $this->_isMissingClockOut($timestamps);
        $this->_isMissingClockIn($timestamps);
        $this->_hasMisMatchBreakPunches($timestamps);
        $this->_hasMisMatchLunchPunches($timestamps);
        $this->_hasTooManyBreakRecords($timestamps);
        $this->_hasTooManyLunchRecords($timestamps);
        $this->_incorrectNumberOfClockInRecords($timestamps);
        $this->_incorrectNumberOfClockOutRecords($timestamps);
        echo 'got this far';
        $this->_clockInClockOutInvalidPunch();
        $this->_lunchInLunchOutInvalidPunch();
        $this->_breakInBreakOutInvalidPunch();

        echo 'got this far 2';
        return $timestamps;
    }

    private function _hasZeroRecords($timestamps)
    {
        if (empty($timestamps)) {
            throw new PayrollShiftEmptyRecordException("No records provided");
        }
    }

    private function _hasTooManyRecords($timestamps)
    {
        $idsPresent = array_unique(array_column($timestamps, 'shift_id'));
        if (count($idsPresent) > 1) {
            throw new PayrollShiftTooManyShiftsException("More than one shift id present in result set");
        }
    }

    private function _hasTooManyEmployeeIds($timestamps)
    {
        $empsPresent = array_unique(array_column($timestamps, 'employee_id'));
        if (count($empsPresent) > 1) {
            throw new PayrollShiftTooManyEmployeesException("More than one employee id present in result set");
        }
    }

    private function _hasIncorrectNumberOfRecords($timestamps)
    {
        $isOdd = boolval(count($timestamps) % 2);
        if (
            count($timestamps) > $this->maxNumberOfRecords ||
            $isOdd
        ) {
            throw new PayrollShiftIncorrectRecordNumberException("Too many, or incorrect number of records");
        }
    }

    private function _isMissingClockOut($timestamps)
    {
        $activityIds = array_column($timestamps, 'activity_id');
        if (!in_array(0, $activityIds)) {
            throw new PayrollShiftMissingPunchException("Missing clock out event");
        }
    }

    private function _isMissingClockIn($timestamps)
    {
        $activityIds = array_column($timestamps, 'activity_id');
        if (!in_array(1, $activityIds)) {
            throw new PayrollShiftMissingPunchException("Missing clock in event");
        }
    }

    private function _hasMisMatchBreakPunches($timestamps)
    {
        $activityCount = array_count_values(array_column($timestamps, 'activity_id'));
        $breakInCount = $activityCount["3"] ?? 0;
        $breakOutCount = $activityCount["-3"] ?? 0;
        if ($breakInCount != $breakOutCount) {
            throw new PayrollShiftMissingPunchException("Mismatch number of break in vs break out");
        }
    }

    private function _hasMisMatchLunchPunches($timestamps)
    {
        $activityCount = array_count_values(array_column($timestamps, 'activity_id'));
        $lunchInCount = $activityCount["5"] ?? 0;
        $lunchOutCount = $activityCount["-5"] ?? 0;
        if ($lunchInCount != $lunchOutCount) {
            throw new PayrollShiftMissingPunchException("Mismatch number of lunch in vs lunch out");
        }
    }

    private function _hasTooManyLunchRecords($timestamps)
    {
        $activityCount = array_count_values(array_column($timestamps, 'activity_id'));
        $lunchInCount = $activityCount["5"] ?? 0;
        $lunchOutCount = $activityCount["-5"] ?? 0;
        if (
            $lunchInCount > $this->maxNumberOfLunches ||
            $lunchOutCount > $this->maxNumberOfLunches
        ) {
            throw new PayrollShiftTooManyRecordsException("Too many lunches");
        }
    }

    private function _hasTooManyBreakRecords($timestamps)
    {
        $activityCount = array_count_values(array_column($timestamps, 'activity_id'));
        $breakInCount = $activityCount["3"] ?? 0;
        $breakOutCount = $activityCount["-3"] ?? 0;
        if (
            $breakInCount > $this->maxNumberOfBreaks ||
            $breakOutCount > $this->maxNumberOfBreaks
        ) {
            throw new PayrollShiftTooManyRecordsException("Too many breaks");
        }
    }

    private function _incorrectNumberOfClockInRecords($timestamps)
    {
        $activityCount = array_count_values(array_column($timestamps, 'activity_id'));
        $clockIns = $activityCount[1] ?? 0;
        if ($clockIns != 1) {
            throw new PayrollShiftTooManyRecordsException("Incorrect number of clock in events");
        }
    }

    private function _incorrectNumberOfClockOutRecords($timestamps)
    {
        $activityCount = array_count_values(array_column($timestamps, 'activity_id'));
        $clockOuts = $activityCount[0] ?? 0;
        if ($clockOuts != 1) {
            throw new PayrollShiftTooManyRecordsException("Incorrect number of clock out events");
        }
    }

    private function _clockInClockOutInvalidPunch()
    {
        $clockIn = current($this->segmentCalculator->extractClockIn());
        $clockOut = current($this->segmentCalculator->extractClockOut());
        if ($clockOut['unix_ts'] < $clockIn['unix_ts']) {
            throw new PayrollShiftImpossibleRecordException('Clock out time value is prior to clock in - Invalid timestamp');
        }
    }

    private function _lunchInLunchOutInvalidPunch()
    {
        $lunchPunches = $this->segmentCalculator->extractLunches();
        if (!empty($lunchPunches)) {
            array_multisort(array_column($lunchPunches, 'activity_id'), SORT_DESC, $lunchPunches);
            $lunchIn = current($lunchPunches);
            $lunchOut = end($lunchPunches);
            if ($lunchOut['unix_ts'] < $lunchIn['unix_ts']) {
                throw new PayrollShiftImpossibleRecordException('Lunch out time value is prior to lunch in - Invalid timestamp');
            }
        }
    }

    private function _breakInBreakOutInvalidPunch()
    {
        $breakPunches = $this->segmentCalculator->extractBreaks();
        if (!empty($breakPunches)) {
            array_multisort(array_column($breakPunches, 'unix_ts'), SORT_ASC, $breakPunches);
            // prime the breakId value. For loop oscillates through the
            // array ensures that there is not two entries in a row
            $breakId = -3;
            for ($ii = 0; $ii < count($breakPunches); $ii++) {
                $curr = $breakPunches[$ii];
                if ($curr['activity_id'] == $breakId) {
                    throw new PayrollShiftImpossibleRecordException('Break out time value is prior to break in - Invalid timestamp');
                } else {
                    $breakId = $curr['activity_id'];
                }
            }
        }
    }
}
