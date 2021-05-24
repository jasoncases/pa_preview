<?php

namespace Proaction\Domain\Timesheets\Model;

use Proaction\Domain\Employees\Model\EmployeeView;
use Proaction\System\Model\ClientModel;

class TimesheetCommentChain extends ClientModel
{
    protected $table = 'ts_comment_chain';
    protected $autoColumns = ['author', 'edited_by'];


    /** =======================================================================
     *
     *                       Public Static Methods
     *
     * ====================================================================== */


    public static function timestampEdit(Timesheet $t, $originalTimestamp)
    {
        return self::p_create([
            'timesheet_id' => $t->id,
            'shift_id' => $t->shift_id,
            'comment' => 'Timestamp [#' . $t->id . '] edited - Previous record: ' . $originalTimestamp,
        ]);
    }

    public static function getCommentsByShiftId(int $shift_id)
    {
        return (new static)->_getCommentsByShiftId($shift_id);
    }

    public static function newComment(string $comment, int $timesheet_id, $author)
    {
        $shift_id = Timesheet::where('id', $timesheet_id)->get('shift_id');
        return TimesheetCommentChain::p_create(compact('comment', 'timesheet_id', 'shift_id'));
    }

    /** =======================================================================
     *
     *                         Private methods
     *
     * ====================================================================== */

    private function _getCommentsByShiftId(int $shift_id)
    {
        $comments = TimesheetCommentChain::where('shift_id', $shift_id)
            ->get(
                [
                    'timesheet_id as stamp_id',
                    'comment',
                    'author',
                    'created_at as date'
                ]
            );
        return $comments->isEmpty() ? null : $this->_appendAuthorData($comments);
    }

    private function _appendAuthorData(array $comments)
    {
        foreach ($comments as $comment) {
            if ($comment->author === 999999) {
                $comment->author = 'SYSTEM NOTICE';
            } else {
                $emp = EmployeeView::where('id', $comment->author)->get('first_name', 'last_name');
                $comment->author = $emp->first_name . ' ' . $emp->last_name;
            }
        }
        return $comments;
    }
}
