<?php

namespace Proaction\System\TicketBasedModules;

use Proaction\Domain\Codes\Model\CodeSearch;
use Proaction\Domain\Users\Model\ProactionUser;
use Proaction\Domain\Tasks\Model\TaskSearch;
use Proaction\Domain\Tickets\Model\TicketSearch;

class Searchable
{
    /**
     * Update TBM searchable record, provide type [task, code, ticket]
     * and the record id
     *
     * Returns INSERT/UPDATE status as boolean
     * 
     * @param string $type
     * @param int $record_id
     * @return boolean
     */
    public static function update($type, $record_id)
    {
        return (new static)->_update($type, $record_id);
    }

    /**
     * Store TBM searchable record, provide type [task, code, ticket]
     * and the record id
     *
     * Returns INSERT/UPDATE status as boolean
     * 
     * @param string $type
     * @param int $record_id
     * @return boolean
     */
    public static function store($type, $record_id)
    {
        return (new static)->_store($type, $record_id);
    }

    private function _update($type, $record_id)
    {
        if (is_array($record_id)) {
            mail(ProactionUser::defaultAdminEmail(), 'Promotion attempted with array for keys: ', print_r(debug_backtrace(), true));
        } else {
            return $this->_model($type)->updateSearch($record_id);
        }
    }

    private function _store($type, $record_id)
    {
        return $this->_model($type)->storeSearch($record_id);
    }

    private function _model($type)
    {
        switch (strtolower(rtrim($type, "s"))) {
            case "ticket":
                return new TicketSearch();
            case "task":
                return new TaskSearch();
            case "code":
                return new CodeSearch();
        }
    }
}
