<?php

namespace Proaction\System\TicketBasedModules;

use Proaction\Domain\Clients\Model\GlobalSetting;
use Proaction\Domain\Users\Resource\UserFactory;


/**
 * Generate the timeline for all ticket-based modules within Proaction. 
 * Since it's possible these modules with further diverge, it was clear
 * we needed a way to make the generator work regardless how different
 * the timelines became. 
 * 
 * Solution was to change the original specific props of priorityChange, 
 * etc to a single `stateChanges` property and then allow the child
 * generator instances to search itself for methods containing the 
 * string 'changes'.  
 * 
 * The results of those queries will be aggregated to the stateChanges
 * property and then merged w/ the post and replies props, sorted and 
 * returned
 * 
 * ! Important - For all child classes, to include timeline items, the
 * ! method contain MUST CONTAIN the string 'changes' (case does not
 * ! matter). 
 * ! AND 
 * ! the returned results MUST CONTAIN a `created_at` column
 */
class TimelineGenerator
{
    protected $record_id, $globals, $post, $replies;
    protected $stateChanges;
    protected $module;
    public function __construct($record_id, $globals = null)
    {
        $this->record_id = $record_id;
        $this->globals = $this->_validateGlobals($globals);
        $this->_init();
    }

    private function _validateGlobals($globals = null) {
        if (!is_null($globals)) {
            return $globals;
        } else {
            return $this->_setGlobals();
        }
    }

    private function _setGlobals() {
        return [
            'timeFormat' => GlobalSetting::get('time_format_long'),
            'timeFormatShort' => GlobalSetting::get('time_format'),
        ];
    }

    private function _init()
    {
        $this->post = $this->_getRecord();
        $this->replies = $this->_getReplies();
        $this->stateChanges = $this->_getAllStateMutations();
    }

    private function _aggregateAllStateMutations($methods)
    {
        $container = [];
        foreach ($methods as $method) { 
            $container = array_merge($container, $this->{$method}() ?: []);
        }
        return $container;
    }

    private function _getAllStateMutations()
    {
        $stateChangeMethods = [];
        foreach (get_class_methods($this) as $method) {
            if (preg_match('/changes/', strtolower($method))) {
                $stateChangeMethods[] = $method;
            }
        }
        return $this->_aggregateAllStateMutations($stateChangeMethods);
    }

    public function get()
    {
        $c = [];
        $admin = UserFactory::create()->isAdmin();
        foreach ($this->_mergeAndSort() as $item) {
            $item['isAdmin'] = $admin;
            $c[] = ['render' => $this->_timeline($item['type'], $item)->generate()];
        }
        return $c;
    }

    private function _timeline($type, $data)
    {
        return new TimelineItem($this->module . '/timeline/' . $type . '.html', $data);
    }

    protected function _mergeAndSort()
    {
        // merge replies and stateChanges to sort them by created date
        $merge = array_merge($this->replies, $this->stateChanges);
        array_multisort(array_column($merge, 'created_at'), SORT_ASC, $merge);
        // merge the post back to the the head
        return array_merge($this->post, $merge);
    }

    protected function _getRecord()
    {
    }

    protected function _getReplies()
    {
    }
}
