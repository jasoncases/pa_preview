<?php

namespace Proaction\Domain\Timesheets\Resource\Timeline;

use Illuminate\Database\Eloquent\Collection;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Helpers\Misc;

/**
 * TimesheetShift is the collection of Timesheet actions for a single
 * Shift that handles the logic of creating TimelineStampCouplet::class
 * which perform the math to create the Timeline segements
 *
 */
class TimelineShift
{
    protected $stamps;
    protected $first;
    protected $last;

    protected $start;
    protected $stamp;
    protected $date;
    protected $action;
    protected $barColor;
    protected $total;
    protected $active;

    public $shift_creation_date;


    /**
     * Shift blocks, clock = all hours, break, lunch & customs
     *
     * @var [type]
     */
    protected $blocks;

    public function __construct(Collection $stamps)
    {
        // set last to the last stamp, without mutating the original
        // collection, we'll use this to determine if the shift is
        // active by checking if this is a closing action
        $this->last = $stamps->last();
        // shift the first one off of the collection, because the first
        // will ALWAYS be the shift opener
        $this->first = $stamps->shift();
        // set the rest of the stamps to the timestamps prop
        $this->timestamps = $stamps;
        $this->_setMeta();
        $this->_createCouplets();
    }

    public function getShiftOutput()
    {
        $c = [];
        foreach ($this->blocks as $block) {
            $c[] = $block->output;
        }
        return $c;
    }

    public function getMinAction()
    {
        $first = current($this->blocks);
        return $first->getStart();
    }

    public function getMaxAction()
    {
        $last = end($this->blocks);
        return $last->getEnd();
    }

    private function _createCouplets()
    {
        $this->_createClockCouplet();
        $this->_createMidCouplets();
    }

    private function _createClockCouplet()
    {
        $this->blocks[] = new TimelineStampCouplet(
            $this->first,
            $this->active ? null : $this->last
        );
    }

    private function _createMidCouplets()
    {
        $len = count($this->timestamps);

        for ($ii = 0; $ii < $len; $ii += 2) {
            $curr = $this->timestamps[$ii];
            $next = $this->timestamps[$ii + 1] ?? null;
            if (isset($next)) {
                if ($curr->activityId === - ($next->activityId)) {
                    $this->blocks[] = new TimelineStampCouplet($curr, $next);
                }
            } else {
                $this->blocks[] = new TimelineStampCouplet($curr, null);
            }
        }
    }
    /**
     * Undocumented function
     *
     * @return void
     */
    private function _setMeta()
    {
        $this->start = Misc::stampToFloat($this->first->stamp);
        $this->active = $this->_setActive();
    }

    private function _setActive()
    {
        return $this->last->activityId != 0;
    }
}
