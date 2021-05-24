<?php 

namespace Proaction\System\TicketBasedModules;

use Proaction\Domain\Codes\Model\CodeCategoryLog;
use Proaction\Domain\Codes\Model\CodePriorityLog;
use Proaction\Domain\Codes\Model\CodeStatusChange;
use Proaction\Domain\Codes\Model\ReplyView;
use Proaction\Domain\Codes\Resource\TimelineItemFactory;
use Proaction\Domain\Users\Resource\UserFactory;

/** 
 * Build and return the Reply and state change timeline for a provided
 * code id
 */
class GenerateTimeline{
     
    private $code_id, $globals;
    private $replies;
    private $stateChanges;
    private $priorityChanges;
    private $categoryChanges;
    private $assigneeChanges;
    public function __construct($code_id, $globals)
    {
        $this->code_id = $code_id;
        $this->globals = $globals;
        $this->_init();
    }

    public function get(){
        $c = [];
        $user = UserFactory::create();
        foreach ($this->_mergeAndSort() as $item) {
            $item['isAdmin'] = $user->isAdmin();
            $c[] = ['render' => TimelineItemFactory::create($item['type'], $item)];
        }
        return $c;
    }

    private function _mergeAndSort(){
        $m = array_merge($this->replies, $this->stateChanges, $this->priorityChanges, $this->categoryChanges);
        array_multisort(array_column($m, 'created_at'), SORT_ASC, $m);
        return $m;
    }

    private function _init(){
        $this->replies = ReplyView::getByCodeId($this->code_id);
        $this->stateChanges = $this->_getStateChanges();
        $this->priorityChanges = $this->_getPriorityChanges();
        $this->categoryChanges = $this->_getCategoryChanges();
    }

    private function _getStateChanges(){
        return CodeStatusChange::getTimeline($this->code_id, $this->globals['timeFormat']);
    }

    private function _getPriorityChanges(){
        return CodePriorityLog::getTimeline($this->code_id, $this->globals['timeFormat']);
    }

    private function _getCategoryChanges(){
        return CodeCategoryLog::getTimeline($this->code_id, $this->globals['timeFormat']);
    }
}